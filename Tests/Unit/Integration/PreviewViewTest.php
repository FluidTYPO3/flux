<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplatePaths;

/**
 * PreviewViewTest
 */
class PreviewViewTest extends AbstractTestCase
{
    public function testInjectsDependencies(): void
    {
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $configurationService = $this->getMockBuilder(FluxService::class)->disableOriginalConstructor()->getMock();
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->injectConfigurationManager($configurationManager);
        $subject->injectConfigurationService($configurationService);
        $subject->injectWorkspacesAwareRecordService($recordService);

        self::assertSame(
            $configurationManager,
            $this->getInaccessiblePropertyValue($subject, 'configurationManager'),
            'ConfigurationManager was not injected'
        );
        self::assertSame(
            $configurationService,
            $this->getInaccessiblePropertyValue($subject, 'configurationService'),
            'FluxService was not injected'
        );
        self::assertSame(
            $recordService,
            $this->getInaccessiblePropertyValue($subject, 'workspacesAwareRecordService'),
            'WorkspacesAwareRecordService was not injected'
        );
    }

    public function testGetPreviewWithModeNone(): void
    {
        $form = Form::create();
        $form->setOption(
            PreviewView::OPTION_PREVIEW,
            [
                PreviewView::OPTION_MODE => PreviewView::MODE_NONE,
                PreviewView::OPTION_TOGGLE => false,
            ]
        );

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['renderPreviewSection'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('renderPreviewSection')->willReturn('preview');

        $record = ['uid' => 123];

        $output = $subject->getPreview($provider, $record);
        self::assertSame('preview', $output);
    }

    /**
     * @dataProvider getGetPreviewWithModeTestValues
     */
    public function testGetPreviewWithMode(string $expected, string $mode): void
    {
        $form = Form::create();
        $form->setOption(
            PreviewView::OPTION_PREVIEW,
            [
                PreviewView::OPTION_MODE => $mode,
                PreviewView::OPTION_TOGGLE => false,
            ]
        );

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn($form);

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['renderPreviewSection', 'getCookie', 'renderGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('renderPreviewSection')->willReturn('preview');
        $subject->method('getCookie')->willReturn('[123]');
        $subject->method('renderGrid')->willReturn('grid');

        $record = ['uid' => 123];

        $output = $subject->getPreview($provider, $record);
        self::assertSame($expected, $output);
    }

    public function getGetPreviewWithModeTestValues(): array
    {
        return [
            'mode prepend' => [
                '<div class="flux-collapse flux-grid-hidden" data-grid-uid="123">grid</div>preview',
                PreviewView::MODE_PREPEND,
            ],
            'mode append' => [
                'preview<div class="flux-collapse flux-grid-hidden" data-grid-uid="123">grid</div>',
                PreviewView::MODE_APPEND,
            ],
        ];
    }

    public function testRenderPreviewSection(): void
    {
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getTemplatePathAndFilename')->willReturn('Tests/Fixtures/Templates/Content/Basic.html');

        $form = Form::create();
        $form->setLabel('Label');

        $languageService = $this->getMockBuilder(LanguageService::class)
            ->setMethods(['sL'])
            ->disableOriginalConstructor()
            ->getMock();
        $languageService->method('sL')->willReturnArgument(0);

        $templatePaths = $this->getMockBuilder(TemplatePaths::class)
            ->setMethods(['fillDefaultsByPackageName', 'setTemplatePathAndFilename'])
            ->disableOriginalConstructor()
            ->getMock();

        $renderingContext = $this->getMockBuilder(RenderingContext::class)
            ->setMethods(['getTemplatePaths', 'setControllerName', 'setControllerAction'])
            ->disableOriginalConstructor()
            ->getMock();
        $renderingContext->method('getTemplatePaths')->willReturn($templatePaths);

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getRenderingContext', 'getLanguageService', 'renderSection'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getLanguageService')->willReturn($languageService);
        $subject->method('getRenderingContext')->willReturn($renderingContext);
        $subject->method('renderSection')->willReturn('preview');

        $output = $this->callInaccessibleMethod($subject, 'renderPreviewSection', $provider, [], $form);
        self::assertSame('preview', $output);
    }

    public function testGetOptionModeReturnsDefaultIfNoValidOptionsFound(): void
    {
        $instance = $this->createInstance();
        $options = array(PreviewView::OPTION_MODE => 'someinvalidvalue');
        $result = $this->callInaccessibleMethod($instance, 'getOptionMode', $options);
        $this->assertEquals(PreviewView::MODE_APPEND, $result);
    }

    /**
     * @test
     */
    public function returnsDefaultsWithoutForm()
    {
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'getPreviewOptions');
        $this->assertEquals(array(
            PreviewView::OPTION_MODE => PreviewView::MODE_APPEND,
            PreviewView::OPTION_TOGGLE => true,
        ), $result);
    }

    /**
     * @test
     */
    public function avoidsRenderPreviewSectionIfTemplateFileDoesNotExist()
    {
        $provider = $this->getMockBuilder(Provider::class)->setMethods(array('getTemplatePathAndFilename'))->getMock();
        $provider->expects($this->atLeastOnce())->method('getTemplatePathAndFilename')->willReturn(null);
        $previewView = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('dummy'))->getMock();
        $this->callInaccessibleMethod($previewView, 'renderPreviewSection', $provider, array());
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instance = $this->getMockBuilder(PreviewView::class)->setMethods(['configurePageLayoutViewForLanguageMode'])->getMock();
        $instance->expects($this->any())->method('configurePageLayoutViewForLanguageMode')->willReturnArgument(0);
        return $instance;
    }
}
