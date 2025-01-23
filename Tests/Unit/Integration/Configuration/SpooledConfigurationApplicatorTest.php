<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\ContentTypeBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\Configuration\ConfigurationContext;
use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Exception;
use TYPO3Fluid\Fluid\View\ViewInterface;

class SpooledConfigurationApplicatorTest extends AbstractTestCase
{
    private SpooledConfigurationApplicator $subject;
    private ContentTypeDefinitionInterface $contentTypeDefinition;
    private ContentTypeDefinitionInterface $contentTypeDefinition2;
    private ContentTypeBuilder $contentTypeBuilder;
    private ContentTypeManager $contentTypeManager;
    private RequestBuilder $requestBuilder;
    private PackageManager $packageManager;
    private CacheService $cacheService;
    private array $singletons = [];

    protected function setUp(): void
    {
        $form = Form::create();
        $form->setOption(FormOption::SORTING, 1);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $this->cacheService = $this->getMockBuilder(CacheService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFromCaches', 'setInCaches', 'remove'])
            ->getMock();

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

        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)->disableOriginalConstructor()->getMock();

        $this->packageManager = $this->getMockBuilder(PackageManager::class)
            ->onlyMethods(['getActivePackages'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageManager->method('getActivePackages')->willReturn([]);

        $this->subject = $this->getMockBuilder(SpooledConfigurationApplicator::class)
            ->onlyMethods(['getApplicationContext'])
            ->setConstructorArgs(
                [
                    $this->contentTypeBuilder,
                    $this->contentTypeManager,
                    $this->requestBuilder,
                    $this->packageManager,
                    $this->cacheService,
                ]
            )
            ->getMock();

        $this->singletons = GeneralUtility::getSingletonInstances();
        $iconRegistry = $this->getMockBuilder(IconRegistry::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::setSingletonInstance(IconRegistry::class, $iconRegistry);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        GeneralUtility::resetSingletonInstances($this->singletons);
    }

    public function testProcessData(): void
    {
        $this->subject->method('getApplicationContext')->willReturn(new ApplicationContext('Development'));
        $this->contentTypeBuilder->expects(self::exactly(2))
            ->method('configureContentTypeFromTemplateFile')
            ->willReturn($this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass());

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
        $this->contentTypeBuilder->expects(self::atLeastOnce())
            ->method('configureContentTypeFromTemplateFile')
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
        $this->contentTypeBuilder->expects(self::atLeastOnce())
            ->method('registerContentType')
            ->willThrowException(new Exception('test'));

        $this->subject->processData();
    }

    public function testSpoolQueuedContentTypeTableConfigurations(): void
    {
        $this->contentTypeBuilder->expects(self::once())->method('addBoilerplateTableConfiguration');
        $this->callInaccessibleMethod(
            $this->subject,
            'spoolQueuedContentTypeTableConfigurations',
            [['FluidTYPO3.Flux', __DIR__ . '/../../../Fixtures/Templates/Content/Default.html', null, 'flux']]
        );
    }
}
