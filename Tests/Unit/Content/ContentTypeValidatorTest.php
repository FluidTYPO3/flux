<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\ContentTypeValidator;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleExtensionManagementUtility;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ErrorHandler\StandardErrorHandler;
use TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\Expression\CastingExpressionNode;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class ContentTypeValidatorTest extends AbstractTestCase
{
    protected ?PackageManager $packageManager;
    protected ?RenderingContextInterface $renderingContext;
    protected ?ContentTypeManager $contentTypeManager;

    protected function setUp(): void
    {
        $this->packageManager = $this->getMockBuilder(PackageManager::class)
            ->setMethods(['isPackageActive'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageManager->method('isPackageActive')->willReturn(true);

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

        $this->renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContext->method('getViewHelperVariableContainer')
            ->willReturn(new ViewHelperVariableContainer());
        $this->renderingContext->method('getTemplatePaths')
            ->willReturn(new TemplatePaths(['templateRootPaths' => ['./']]));
        $this->renderingContext->method('getVariableProvider')->willReturn(new StandardVariableProvider());
        $this->renderingContext->method('getTemplateCompiler')->willReturn(new TemplateCompiler());
        $this->renderingContext->method('getCache')->willReturn(
            $this->getMockBuilder(FluidCacheInterface::class)->getMockForAbstractClass()
        );
        $this->renderingContext->method('getTemplateParser')->willReturn(new TemplateParser());
        $this->renderingContext->method('getViewHelperResolver')->willReturn(new ViewHelperResolver());
        $this->renderingContext->method('getErrorHandler')->willReturn(new StandardErrorHandler());
        $this->renderingContext->method('getTemplateProcessors')->willReturn([]);
        $this->renderingContext->method('getExpressionNodeTypes')->willReturn([CastingExpressionNode::class]);

        $this->renderingContext->getTemplateParser()->setRenderingContext($this->renderingContext);
        $this->renderingContext->getTemplateCompiler()->setRenderingContext($this->renderingContext);
        $this->renderingContext->getTemplatePaths()
            ->setTemplateSource(<<<SOURCE
Result:
    new={recordIsNew as integer},
    templateFile={validation.templateFile},
    templateSource={validation.templateSource},
    usages={usages},
SOURCE
            );

        AccessibleExtensionManagementUtility::setPackageManager($this->packageManager);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        AccessibleExtensionManagementUtility::setPackageManager(null);

        parent::tearDown();
    }

    public function testValidatesTemplateSourceWithNewRecordRendersNewRecordView(): void
    {
        $parameters = [
            'row' => [
                'uid' => 'NEW123',
                'content_type' => 'test_foobar',
            ],
        ];
        $subject = new ContentTypeValidator();
        $rendered = $subject->validateContentTypeRecord($parameters);
        $expected = <<<SOURCE
Result:
    new=1,
    templateFile=,
    templateSource=,
    usages=,
SOURCE;
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
        $subject = new ContentTypeValidator();
        $rendered = $subject->validateContentTypeRecord($parameters);
        $expected = <<<SOURCE
Result:
    new=1,
    templateFile=,
    templateSource=,
    usages=,
SOURCE;
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
        $subject = $this->getMockBuilder(ContentTypeValidator::class)
            ->setMethods(['resolveAbsolutePathForFilename', 'countUsages'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('resolveAbsolutePathForFilename')->willReturn('./ext_tables.sql');
        $subject->method('countUsages')->willReturn(5);
        $rendered = $subject->validateContentTypeRecord($parameters);
        $expected = <<<SOURCE
Result:
    new=0,
    templateFile=1,
    templateSource=,
    usages=5,
SOURCE;
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
        $subject = $this->getMockBuilder(ContentTypeValidator::class)
            ->setMethods(['resolveAbsolutePathForFilename', 'countUsages'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('resolveAbsolutePathForFilename')->willReturn('./ext_tables.sql');
        $subject->method('countUsages')->willReturn(5);
        $rendered = $subject->validateContentTypeRecord($parameters);
        $expected = <<<SOURCE
Result:
    new=0,
    templateFile=1,
    templateSource=,
    usages=5,
SOURCE;
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
        $subject = $this->getMockBuilder(ContentTypeValidator::class)
            ->setMethods(['resolveAbsolutePathForFilename', 'countUsages'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('resolveAbsolutePathForFilename')->willReturn('./ext_tables.sql');
        $subject->method('countUsages')->willReturn(5);
        $rendered = $subject->validateContentTypeRecord($parameters);
        $errorMessage = 'Fluid parse error in template , line 1 at character 1. Error: The ViewHelper "<f:invalid>" '
            . 'could not be resolved.'
            . PHP_EOL
            . 'Based on your spelling, the system would load the class '
            . '"TYPO3Fluid\Fluid\ViewHelpers\InvalidViewHelper", however this class does not exist. '
            . '(error code 1407060572). Template source chunk: <f:invalid>';
        $expected = <<<SOURCE
Result:
    new=0,
    templateFile=1,
    templateSource={$errorMessage},
    usages=5,
SOURCE;
        self::assertSame($expected, $rendered);
    }

    protected function createObjectManagerInstance(): ObjectManagerInterface
    {
        $instance = parent::createObjectManagerInstance();
        $instance->method('get')->willReturnMap(
            [
                [Request::class, $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock()],
                [ControllerContext::class, new ControllerContext()],
                [TemplateView::class, new TemplateView($this->renderingContext)],
                [ContentTypeManager::class, $this->contentTypeManager],
            ]
        );
        return $instance;
    }
}
