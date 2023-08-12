<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Integration\NormalizedData\Converter\InlineRecordDataConverter;
use FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation;
use FluidTYPO3\Flux\Integration\NormalizedData\ImplementationRegistry;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyPageController;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class DataAccessTraitTest extends AbstractTestCase
{
    /**
     * @var FluxService&MockObject
     */
    protected FluxService $fluxService;

    /**
     * @var RenderingContextBuilder&MockObject
     */
    protected RenderingContextBuilder $renderingContextBuilder;

    /**
     * @var RequestBuilder&MockObject
     */
    protected RequestBuilder $requestBuilder;

    protected TypoScriptService $typoScriptService;

    protected function setUp(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['flux'][ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE] = 1;

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn(Form::create());

        $providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $this->singletonInstances[ProviderResolver::class] = $providerResolver;
        $this->renderingContextBuilder = $this->getMockBuilder(RenderingContextBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->onlyMethods(['getEnvironmentVariable'])
            ->getMock();
        $this->requestBuilder->method('getEnvironmentVariable')->willReturn('env');
        $this->typoScriptService = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getSettingsForExtensionName', 'getTypoScriptByPath'])
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TYPO3_REQUEST'] = $this->getMockBuilder(ServerRequest::class)->getMockForAbstractClass();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']);
    }

    protected function getControllerConstructorArguments(): array
    {
        return [
            $this->fluxService,
            $this->renderingContextBuilder,
            $this->requestBuilder,
            $this->getMockBuilder(WorkspacesAwareRecordService::class)->disableOriginalConstructor()->getMock(),
            $this->typoScriptService,
        ];
    }

    public function testTraitThrowsUnexpectedValueExceptionOnMissingRecord(): void
    {
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)
            ->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')->willReturn(['foo' => 'bar']);
        $configurationManager->method('getContentObject')->willReturn(null);

        $subject = new DummyPageController(...$this->getControllerConstructorArguments());

        self::expectExceptionCode(1666538343);
        $subject->injectConfigurationManager($configurationManager);
    }

    public function testTraitBehavior(): void
    {
        FlexFormImplementation::registerForTableAndField('pages', 'tx_fed_page_flexform');
        ImplementationRegistry::registerImplementation(FlexFormImplementation::class, ['foo' => 'bar']);

        $contentObject = $this->getMockBuilder(ContentObjectRenderer::class)->disableOriginalConstructor()->getMock();
        $contentObject->method('getCurrentTable')->willReturn('tt_content');
        $contentObject->data = ['uid' => 123];

        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)
            ->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')->willReturn(['foo' => 'bar']);
        $configurationManager->method('getContentObject')->willReturn($contentObject);

        $converter = $this->getMockBuilder(InlineRecordDataConverter::class)->disableOriginalConstructor()->getMock();
        $converter->method('convertData')->willReturnArgument(0);

        $flexFormImplementation = $this->getMockBuilder(FlexFormImplementation::class)
            ->setMethods(['getConverterForTableFieldAndRecord'])
            ->disableOriginalConstructor()
            ->getMock();

        $formDataTransformer = $this->getMockBuilder(FormDataTransformer::class)
            ->setMethods(['transformAccordingToConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $formDataTransformer->expects(self::once())
            ->method('transformAccordingToConfiguration')
            ->willReturnArgument(0);

        $subject = new DummyPageController(...$this->getControllerConstructorArguments());

        GeneralUtility::addInstance(FormDataTransformer::class, $formDataTransformer);
        GeneralUtility::addInstance(FlexFormImplementation::class, $flexFormImplementation);

        $subject->injectConfigurationManager($configurationManager);
    }
}
