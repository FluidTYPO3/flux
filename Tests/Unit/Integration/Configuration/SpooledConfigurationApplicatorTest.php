<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\Configuration\ConfigurationContext;
use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Exception;
use TYPO3Fluid\Fluid\View\ViewInterface;

class SpooledConfigurationApplicatorTest extends AbstractTestCase
{
    private FluxService $fluxService;
    private CacheManager $cacheManager;
    private SpooledConfigurationApplicator $subject;
    private ContentTypeDefinitionInterface $contentTypeDefinition;
    private ContentTypeDefinitionInterface $contentTypeDefinition2;
    private ContentTypeBuilder $contentTypeBuilder;
    private ContentTypeManager $contentTypeManager;

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager = $this->getMockBuilder(CacheManager::class)
            ->onlyMethods(['getCache'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManager->method('getCache')
            ->willReturn($this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass());

        $form = Form::create();
        $form->setOption(Form::OPTION_SORTING, 1);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $this->contentTypeBuilder = $this->getMockBuilder(ContentTypeBuilder::class)
            ->onlyMethods(
                [
                    'configureContentTypeFromTemplateFile',
                    'registerContentType',
                    'addBoilerplateTableConfiguration',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentTypeBuilder->method('configureContentTypeFromTemplateFile')->willReturn($provider);

        GeneralUtility::addInstance(ContentTypeBuilder::class, $this->contentTypeBuilder);
        GeneralUtility::addInstance(ContentTypeBuilder::class, $this->contentTypeBuilder);

        $view = $this->getMockBuilder(ViewInterface::class)->getMockForAbstractClass();

        $viewBuilder = $this->getMockBuilder(ViewBuilder::class)
            ->onlyMethods(['buildTemplateView'])
            ->disableOriginalConstructor()
            ->getMock();
        $viewBuilder->method('buildTemplateView')->willReturn($view);

        $this->contentTypeDefinition = $this->getMockBuilder(FluidRenderingContentTypeDefinitionInterface::class)
            ->getMockForAbstractClass();
        $this->contentTypeDefinition->method('getExtensionIdentity')->willReturn('FluidTYPO3.Flux');
        $this->contentTypeDefinition->method('getProviderClassName')->willReturn(DummyConfigurationProvider::class);
        $this->contentTypeDefinition->method('getForm')->willReturn(Form::create());
        $this->contentTypeDefinition->method('getTemplatePathAndFilename')->willReturn(
            __DIR__ . '/../../../Fixtures/Templates/Content/Default.html'
        );

        $this->contentTypeDefinition2 = clone $this->contentTypeDefinition;

        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->onlyMethods(['fetchContentTypes', 'registerTypeDefinition'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentTypeManager->method('fetchContentTypes')
            ->willReturn([$this->contentTypeDefinition, $this->contentTypeDefinition2]);

        $configurationContext = new ConfigurationContext();

        $this->subject = $this->getMockBuilder(SpooledConfigurationApplicator::class)
            ->onlyMethods(['getApplicationContext', 'getContentTypeManager'])
            ->setConstructorArgs([$configurationContext])
            ->getMock();
        $this->subject->method('getContentTypeManager')->willReturn($this->contentTypeManager);


        parent::setUp();
    }

    public function testProcessData(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));
        $this->contentTypeManager->expects(self::exactly(2))
            ->method('registerTypeDefinition')
            ->withConsecutive([$this->contentTypeDefinition], [$this->contentTypeDefinition2]);

        $this->subject->processData();
    }

    public function testProcessDataReThrowsFluidExceptionInDevelopmentContextWhenConfiguring(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));
        $this->contentTypeBuilder->method('configureContentTypeFromTemplateFile')
            ->willThrowException(new Exception('test'));
        self::expectExceptionMessage('test');

        $this->subject->processData();
    }

    public function testProcessDataSwallowsFluidExceptionInProductionContextWhenConfiguring(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Production'));
        $this->contentTypeManager->expects(self::exactly(2))
            ->method('registerTypeDefinition')
            ->withConsecutive([$this->contentTypeDefinition], [$this->contentTypeDefinition2]);
        $this->contentTypeBuilder->method('configureContentTypeFromTemplateFile')
            ->willThrowException(new Exception('test'));

        $this->subject->processData();
    }

    public function testProcessDataReThrowsFluidExceptionInDevelopmentContextWhenRegistering(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));
        $this->contentTypeBuilder->method('registerContentType')
            ->willThrowException(new Exception('test'));
        self::expectExceptionMessage('test');

        $this->subject->processData();
    }

    public function testProcessDataSwallowsFluidExceptionInProductionContextWhenRegistering(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Production'));
        $this->contentTypeManager->expects(self::exactly(2))
            ->method('registerTypeDefinition')
            ->withConsecutive([$this->contentTypeDefinition], [$this->contentTypeDefinition2]);
        $this->contentTypeBuilder->method('registerContentType')
            ->willThrowException(new Exception('test'));

        $this->subject->processData();
    }

    public function testSpoolQueuedContentTypeTableConfigurations(): void
    {
        $this->contentTypeBuilder->expects(self::once())->method('addBoilerplateTableConfiguration');
        SpooledConfigurationApplicator::spoolQueuedContentTypeTableConfigurations(
            [['FluidTYPO3.Flux', __DIR__ . '/../../../Fixtures/Templates/Content/Default.html', null, 'flux']]
        );
    }
}
