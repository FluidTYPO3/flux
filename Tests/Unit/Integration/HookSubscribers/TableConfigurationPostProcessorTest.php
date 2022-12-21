<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\FluidFileBasedContentTypeDefinition;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleTableConfigurationPostProcessor;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3Fluid\Fluid\Exception;

/**
 * TableConfigurationPostProcessorTest
 */
class TableConfigurationPostProcessorTest extends AbstractTestCase
{
    protected $contentTypeBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contentTypeBuilder = $this->getMockBuilder(ContentTypeBuilder::class)
            ->setMethods(
                [
                    'addBoilerplateTableConfiguration',
                    'configureContentTypeFromTemplateFile',
                    'registerContentType',
                ]
            )
            ->getMock();
        AccessibleTableConfigurationPostProcessor::setContentTypeBuilder($this->contentTypeBuilder);
        $GLOBALS['LANG'] = $this->getMockBuilder(LanguageService::class)
            ->setMethods(['sL'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Core::clearQueuedContentTypeRegistrations();
        AccessibleTableConfigurationPostProcessor::setContentTypeBuilder(null);
    }

    public function testIncludeStaticTypoScriptHook(): void
    {
        $subject = $this->getMockBuilder(TableConfigurationPostProcessor::class)
            ->setMethods(['processData'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('processData');
        $templateService = $this->getMockBuilder(TemplateService::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->includeStaticTypoScriptHook([], $templateService);
    }

    public function testSpoolQueuedContentTypeTableConfigurations(): void
    {
        $singletons = GeneralUtility::getSingletonInstances();

        $queue = [
            [
                'FluidTYPO3.Flux',
                'Tests/Fixtures/Templates/Content/Default.html',
                null,
                null,
            ],
        ];

        $this->contentTypeBuilder->expects(self::once())
            ->method('addBoilerplateTableConfiguration')
            ->with('flux_default');

        TableConfigurationPostProcessor::spoolQueuedContentTypeTableConfigurations($queue);
    }

    public function testProcessData(?Exception $exception1 = null, ?Exception $exception2 = null): void
    {
        $contentType1 = $this->getMockBuilder(FluidFileBasedContentTypeDefinition::class)
            ->setMethods(
                [
                    'getExtensionIdentity',
                    'getTemplatePathAndFilename',
                    'getContentTypeName',
                    'getProviderClassName'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $contentType1->method('getExtensionIdentity')->willReturn('FluidTYPO3.Flux');
        $contentType1->method('getTemplatePathAndFilename')
            ->willReturn('Tests/Fixtures/Templates/Content/Default.html');
        $contentType1->method('getContentTypeName')->willReturn('flux_default');
        $contentType1->method('getProviderClassName')->willReturn(Provider::class);

        $contentType2 = clone $contentType1;
        $contentType2->method('getContentTypeName')->willReturn('flux_second');

        $contentType3 = clone $contentType1;
        $contentType3->method('getContentTypeName')->willReturn('flux_third');

        $contentTypes = [
            'a' => $contentType1,
            'b' => $contentType2,
            'c' => $contentType3,
        ];

        $contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->setMethods(['fetchContentTypes'])
            ->disableOriginalConstructor()
            ->getMock();
        $contentTypeManager->method('fetchContentTypes')->willReturn($contentTypes);

        $provider1 = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider1->method('getExtensionKey')->willReturn('FluidTYPO3.Flux');
        $provider1->method('getContentObjectType')->willReturn('flux_default');

        $provider2 = clone $provider1;
        $provider2->method('getContentObjectType')->willReturn('flux_second');
        $provider2->method('getForm')->willReturn(null);

        $provider3 = clone $provider1;
        $provider3->method('getContentObjectType')->willReturn('flux_third');
        $provider3->method('getForm')->willReturn(Form::create()->setOption(Form::OPTION_SORTING, ['invalidvalue']));

        $provider1->method('getForm')->willReturn(Form::create()->setOption(Form::OPTION_SORTING, 1));

        if ($exception1 !== null) {
            self::expectExceptionObject($exception1);
            $this->contentTypeBuilder->method('configureContentTypeFromTemplateFile')->willThrowException($exception1);
        } else {
            $this->contentTypeBuilder->method('configureContentTypeFromTemplateFile')
                ->willReturnOnConsecutiveCalls($provider1, $provider2, $provider3);
        }

        if ($exception2 !== null) {
            self::expectExceptionObject($exception2);
            $this->contentTypeBuilder->method('registerContentType')->willThrowException($exception2);
        } elseif ($exception1 === null) {
            $this->contentTypeBuilder->expects(self::exactly(3))->method('registerContentType');
        }

        $subject = $this->getMockBuilder(TableConfigurationPostProcessor::class)
            ->setMethods(['getContentTypeManager', 'getApplicationContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getContentTypeManager')->willReturn($contentTypeManager);
        $subject->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));

        $subject->processData();
    }

    public function testProcessDataWithExeptionInLoop1(): void
    {
        $this->testProcessData(new Exception('test'));
    }

    public function testProcessDataWithExeptionInLoop2(): void
    {
        $this->testProcessData(null, new Exception('test'));
    }

    /**
     * @test
     */
    public function canLoadProcessorAsUserObject()
    {
        $manager = $this->getMockBuilder(ContentTypeManager::class)->setMethods(['fetchContentTypes'])->getMock();
        $manager->expects($this->atLeastOnce())->method('fetchContentTypes')->willReturn([]);
        $object = $this->getMockBuilder(TableConfigurationPostProcessor::class)
            ->setMethods(['getContentTypeManager', 'getApplicationContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->atLeastOnce())->method('getContentTypeManager')->willReturn($manager);
        $object->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));
        $object->processData();
    }
}
