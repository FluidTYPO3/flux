<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\ContentTypeValidator;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Service\TemplateValidationService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Fluid\View\TemplateView;

class ContentTypeValidatorTest extends AbstractTestCase
{
    protected PackageManager $packageManager;
    protected ContentTypeManager $contentTypeManager;
    protected TemplateValidationService $templateValidationService;
    protected ContentTypeValidator $subject;

    protected function setUp(): void
    {
        $contentTypeDefinition = $this->getMockBuilder(RecordBasedContentTypeDefinition::class)
            ->setMethods(
                [
                    'isUsingTemplateFile',
                    'getTemplatePathAndFilename',
                    'getExtensionIdentity',
                    'getContentConfiguration',
                    'getGridConfiguration',
                    'getTemplateSource'
                ]
            )
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $errorContentTypeDefinition = clone $contentTypeDefinition;
        $notRecordBasedContentTypeDefinition = $this->getMockBuilder(
            FluidRenderingContentTypeDefinitionInterface::class
        )->getMockForAbstractClass();
        foreach ([$contentTypeDefinition, $errorContentTypeDefinition, $notRecordBasedContentTypeDefinition] as $def) {
            $def->method('getExtensionIdentity')->willReturn('flux');
            $def->method('isUsingTemplateFile')->willReturn(true);
            $def->method('getTemplatePathAndFilename')->willReturn('./Tests/Fixtures/Page/Dummy.html');
        }
        $contentTypeDefinition->method('getContentConfiguration')->willReturn([]);
        $contentTypeDefinition->method('getGridConfiguration')->willReturn([]);
        $errorContentTypeDefinition->method('getContentConfiguration')->willReturn([]);
        $errorContentTypeDefinition->method('getGridConfiguration')->willReturn([]);
        $errorContentTypeDefinition->method('getTemplateSource')->willReturn('<f:invalid>');

        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['determineContentTypeForTypeString'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentTypeManager->method('determineContentTypeForTypeString')->willReturnMap(
            [
                ['test_foobar', null],
                ['test_error', $errorContentTypeDefinition],
                ['test_notrecordbased', $notRecordBasedContentTypeDefinition],
                ['test_matched', $contentTypeDefinition],
            ]
        );

        $this->templateValidationService = $this->getMockBuilder(TemplateValidationService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateView = $this->getMockBuilder(TemplateView::class)
            ->setMethods(['render', 'assign', 'assignMultiple'])
            ->disableOriginalConstructor()
            ->getMock();
        $templateView->method('render')->willReturn('rendered');
        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->setMethods(['buildTemplateView'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewBuilder->method('buildTemplateView')->willReturn($templateView);

        $this->singletonInstances[TemplateValidationService::class] = $this->templateValidationService;
        $this->singletonInstances[ContentTypeManager::class] = $this->contentTypeManager;

        if (class_exists(ControllerContext::class)) {
            GeneralUtility::addInstance(ControllerContext::class, new ControllerContext());
        }

        $this->subject = $this->getMockBuilder(ContentTypeValidator::class)
            ->onlyMethods(['validateContextExtensionIsInstalled', 'resolveAbsolutePathForFilename', 'countUsages'])
            ->setConstructorArgs(
                [
                    $viewBuilder,
                    $this->contentTypeManager,
                    $this->getMockBuilder(ConnectionPool::class)->disableOriginalConstructor()->getMock(),
                    $this->templateValidationService
                ]
            )
            ->getMock();

        parent::setUp();
    }

    public function testValidatesTemplateSourceWithNewRecordRendersNewRecordView(): void
    {
        $parameters = [
            'row' => [
                'uid' => 'NEW123',
                'content_type' => 'test_foobar',
            ],
        ];

        $rendered = $this->subject->validateContentTypeRecord($parameters);
        $expected = 'rendered';
        self::assertSame($expected, $rendered);
    }

    public function testValidatesTemplateSourceWithUnmatchedContentTypeRendersNewRecordView(): void
    {
        $parameters = [
            'row' => [
                'uid' => 123,
                'content_type' => 'test_foobar',
            ],
        ];

        $rendered = $this->subject->validateContentTypeRecord($parameters);
        $expected = 'rendered';
        self::assertSame($expected, $rendered);
    }

    public function testValidatesTemplateSourceWithMatchedContentTypeRendersValidationInfo(): void
    {
        $parameters = [
            'row' => [
                'uid' => 123,
                'content_type' => 'test_matched',
            ],
        ];

        $this->subject->method('resolveAbsolutePathForFilename')->willReturn(__DIR__ . '/../../../ext_tables.sql');
        $this->subject->method('countUsages')->willReturn(5);

        $rendered = $this->subject->validateContentTypeRecord($parameters);
        $expected = 'rendered';
        self::assertSame($expected, $rendered);
    }

    public function testValidatesTemplateSourceWithContentTypeNotRecordBasedRendersValidationInfo(): void
    {
        $parameters = [
            'row' => [
                'uid' => 123,
                'content_type' => 'test_notrecordbased',
            ],
        ];

        $this->subject->method('resolveAbsolutePathForFilename')->willReturn(__DIR__ . '/../../../ext_tables.sql');
        $this->subject->method('countUsages')->willReturn(5);

        $rendered = $this->subject->validateContentTypeRecord($parameters);
        $expected = 'rendered';
        self::assertSame($expected, $rendered);
    }

    public function testValidatesTemplateSourceAndRendersErrorOnInvalidTemplateSource(): void
    {
        $parameters = [
            'row' => [
                'uid' => 123,
                'content_type' => 'test_error',
            ],
        ];

        $this->subject->method('resolveAbsolutePathForFilename')->willReturn(__DIR__ . '/../../../ext_tables.sql');
        $this->subject->method('countUsages')->willReturn(5);

        $rendered = $this->subject->validateContentTypeRecord($parameters);
        $errorMessage = 'Fluid parse error in template , line 1 at character 1. Error: The ViewHelper "<f:invalid>" '
            . 'could not be resolved.'
            . PHP_EOL
            . 'Based on your spelling, the system would load the class '
            . '"TYPO3Fluid\Fluid\ViewHelpers\InvalidViewHelper", however this class does not exist. '
            . '(error code 1407060572). Template source chunk: <f:invalid>';
        $expected = 'rendered';
        self::assertSame($expected, $rendered);
    }
}
