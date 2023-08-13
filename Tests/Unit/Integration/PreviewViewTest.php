<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\PreviewOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\BackendLayoutRenderer;
use FluidTYPO3\Flux\Integration\Overrides\PageLayoutView;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\Drawing\DrawingConfiguration;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\View\TemplatePaths;

class PreviewViewTest extends AbstractTestCase
{
    private ConfigurationManager $configurationManager;
    private WorkspacesAwareRecordService $recordService;

    protected function setUp(): void
    {
        $this->configurationManager
            = $this->singletonInstances[ConfigurationManager::class]
            = $this->getMockBuilder(ConfigurationManager::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->recordService
            = $this->singletonInstances[WorkspacesAwareRecordService::class]
            = $this->getMockBuilder(WorkspacesAwareRecordService::class)
                ->setMethods(['getSingle'])
                ->disableOriginalConstructor()
                ->getMock();

        $GLOBALS['TYPO3_REQUEST'] = new ServerRequest();

        parent::setUp();
    }

    public function testInjectsDependencies(): void
    {
        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['dummy'])
            ->getMock();

        self::assertSame(
            $this->configurationManager,
            $this->getInaccessiblePropertyValue($subject, 'configurationManager'),
            'ConfigurationManager was not injected'
        );
        self::assertSame(
            $this->recordService,
            $this->getInaccessiblePropertyValue($subject, 'workspacesAwareRecordService'),
            'WorkspacesAwareRecordService was not injected'
        );
    }

    public function testGetPreviewWithModeNone(): void
    {
        $form = Form::create();
        $form->setOption(
            PreviewOption::PREVIEW,
            [
                PreviewOption::MODE => PreviewOption::MODE_NONE,
                PreviewOption::TOGGLE => false,
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
            PreviewOption::PREVIEW,
            [
                PreviewOption::MODE => $mode,
                PreviewOption::TOGGLE => false,
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
                PreviewOption::MODE_PREPEND,
            ],
            'mode append' => [
                'preview<div class="flux-collapse flux-grid-hidden" data-grid-uid="123">grid</div>',
                PreviewOption::MODE_APPEND,
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
        $options = [];
        $result = $this->callInaccessibleMethod($instance, 'getOptionMode', $options);
        $this->assertEquals(PreviewOption::MODE_APPEND, $result);
    }

    public function testRenderGridWithoutChildren(): void
    {
        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn(Form\Container\Grid::create());
        $form = Form::create();
        self::assertSame('', $this->callInaccessibleMethod($subject, 'renderGrid', $provider, ['uid' => 123], $form));
    }

    public function testRenderGridWithChildrenWorkspaceEnabled(): void
    {
        if (!class_exists(\TYPO3\CMS\Backend\View\PageLayoutView::class)) {
            $this->markTestSkipped('Skipping test with PageLayoutView dependency');
        }

        $renderer = $this->getMockBuilder(BackendLayoutRenderer::class)
            ->setMethods(['drawContent', 'getTable_tt_content', 'getContext'])
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->method('drawContent')->willReturn('rendered');
        $renderer->method('getTable_tt_content')->willReturn('rendered');
        $renderer->method('getContext')->willReturn(
            $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock()
        );

        $backendUser = $this->getMockBuilder(BackendUserAuthentication::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendUser->workspace = 1;

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getInitializedPageLayoutView', 'fetchWorkspaceVersionOfRecord', 'getBackendUser'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getInitializedPageLayoutView')->willReturn($renderer);
        $subject->method('fetchWorkspaceVersionOfRecord')->willReturn(null);
        $subject->method('getBackendUser')->willReturn($backendUser);

        $grid = Form\Container\Grid::create();
        /** @var Form\Container\Column $column */
        $column = $grid->createContainer(Form\Container\Row::class, 'row')
            ->createContainer(Form\Container\Column::class, 'column');
        $column->setColumnPosition(123);
        $form = Form::create();

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = [];

        $output = $this->callInaccessibleMethod($subject, 'renderGrid', $provider, ['uid' => 123, 'pid' => 1], $form);

        self::assertSame('<div class="grid-visibility-toggle" data-toggle-uid="123"></div>rendered', $output);
    }

    /**
     * @dataProvider getRenderGridWithChildrenTestValues
     * @param BackendLayoutRenderer|PageLayoutView $renderer
     * @return void
     */
    public function testRenderGridWithChildren($renderer): void
    {
        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getInitializedPageLayoutView', 'getBackendUser'])
            ->getMock();
        $subject->method('getInitializedPageLayoutView')->willReturn($renderer);
        $grid = Form\Container\Grid::create();
        /** @var Form\Container\Column $column */
        $column = $grid->createContainer(Form\Container\Row::class, 'row')
            ->createContainer(Form\Container\Column::class, 'column');
        $column->setColumnPosition(123);
        $form = Form::create();

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] = [];

        $output = $this->callInaccessibleMethod($subject, 'renderGrid', $provider, ['uid' => 123, 'pid' => 1], $form);

        self::assertSame('<div class="grid-visibility-toggle" data-toggle-uid="123"></div>rendered', $output);
    }

    public function getRenderGridWithChildrenTestValues(): array
    {
        if (!class_exists(\TYPO3\CMS\Backend\View\PageLayoutView::class)) {
            $this->markTestSkipped('Skipping test with PageLayoutView dependency');
        }

        $backendLayoutRenderer = $this->getMockBuilder(BackendLayoutRenderer::class)
            ->setMethods(['drawContent', 'getTable_tt_content'])
            ->disableOriginalConstructor()
            ->getMock();
        $backendLayoutRenderer->method('drawContent')->willReturn('rendered');
        $backendLayoutRenderer->method('getTable_tt_content')->willReturn('rendered');

        $pageLayoutView = $this->getMockBuilder(PageLayoutView::class)
            ->setMethods(['getTable_tt_content', 'generateList'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageLayoutView->method('getTable_tt_content')->willReturn('rendered');

        $legacyPageLayoutView = $this->getMockBuilder(PageLayoutView::class)
            ->setMethods(['generateList'])
            ->addMethods(['start'])
            ->disableOriginalConstructor()
            ->getMock();
        $legacyPageLayoutView->HTMLcode = 'rendered';

        return [
            'with backend layout renderer' => [$backendLayoutRenderer],
            'with page layout view' => [$pageLayoutView],
            'with legacy page layout view' => [$legacyPageLayoutView],
        ];
    }

    public function testGetInitializedPageLayoutViewWithFluidPageModuleFeatureEnabled(): void
    {
        $record = [
            'uid' => 123,
            'pid' => 1,
            'l18n_parent' => 0,
            't3ver_oid' => 0,
            'sys_language_uid' => 0,
        ];

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();

        $this->recordService->method('getSingle')->willReturn(['uid' => 1]);

        $backendUser = $this->getMockBuilder(BackendUserAuthentication::class)
            ->setMethods(['getModuleData'])
            ->disableOriginalConstructor()
            ->getMock();

        $renderer = $this->getMockBuilder(BackendLayoutRenderer::class)
            ->setMethods(['drawContent', 'getTable_tt_content'])
            ->disableOriginalConstructor()
            ->getMock();
        $renderer->method('drawContent')->willReturn('rendered');
        $renderer->method('getTable_tt_content')->willReturn('rendered');

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getBackendUser', 'fetchPageRecordWithoutOverlay', 'createBackendLayoutRenderer'])
            ->getMock();
        $subject->method('fetchPageRecordWithoutOverlay')->willReturn(['uid' => 456]);
        $subject->method('createBackendLayoutRenderer')->willReturn($renderer);

        $features = $this->getMockBuilder(Features::class)
            ->setMethods(['isFeatureEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $features->method('isFeatureEnabled')->willReturn(true);
        GeneralUtility::addInstance(Features::class, $features);

        $siteFinder = $this->getMockBuilder(SiteFinder::class)
            ->setMethods(['getSiteByPageId'])
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::addInstance(SiteFinder::class, $siteFinder);

        $pageLayoutContext = $this->getMockBuilder(PageLayoutContext::class)
            ->setMethods(['getDrawingConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $pageLayoutContext->method('getDrawingConfiguration')->willReturn(new DrawingConfiguration());
        GeneralUtility::addInstance(PageLayoutContext::class, $pageLayoutContext);

        $output = $this->callInaccessibleMethod($subject, 'getInitializedPageLayoutView', $provider, $record);
        self::assertSame($renderer, $output);
    }

    public function testGetInitializedPageLayoutViewWithFluidPageModuleFeatureDisabled(): void
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5', '>')) {
            $this->markTestSkipped('Skipping test with PageLayoutView dependency');
        }
        $singletonInstances = GeneralUtility::getSingletonInstances();

        $record = [
            'uid' => 123,
            'pid' => 1,
            'l18n_parent' => 0,
            't3ver_oid' => 0,
            'sys_language_uid' => 0,
        ];

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();

        $languageService = $this->getMockBuilder(LanguageService::class)
            ->setMethods(['sL'])
            ->disableOriginalConstructor()
            ->getMock();

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $recordService->method('getSingle')->willReturn(['uid' => 1]);
        GeneralUtility::setSingletonInstance(WorkspacesAwareRecordService::class, $recordService);

        $backendUser = $this->getMockBuilder(BackendUserAuthentication::class)
            ->setMethods(['getModuleData'])
            ->disableOriginalConstructor()
            ->getMock();

        $subject = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['getBackendUser', 'fetchPageRecordWithoutOverlay', 'getLanguageService', 'checkAccessToPage'])
            ->getMock();
        $subject->method('fetchPageRecordWithoutOverlay')->willReturn(['uid' => 456]);
        $subject->method('getLanguageService')->willReturn($languageService);
        $subject->method('checkAccessToPage')->willReturn(['read' => true]);

        $features = $this->getMockBuilder(Features::class)
            ->setMethods(['isFeatureEnabled'])
            ->disableOriginalConstructor()
            ->getMock();
        $features->method('isFeatureEnabled')->willReturn(false);
        GeneralUtility::addInstance(Features::class, $features);

        $eventDispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::setSingletonInstance(EventDispatcher::class, $eventDispatcher);

        $renderer = $this->getMockBuilder(PageLayoutView::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        GeneralUtility::addInstance(PageLayoutView::class, $renderer);

        $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] = [
            [
                'foo',
                'bar',
                'baz',
            ],
        ];

        $output = $this->callInaccessibleMethod($subject, 'getInitializedPageLayoutView', $provider, $record);

        GeneralUtility::resetSingletonInstances($singletonInstances);

        self::assertSame($renderer, $output);
    }

    /**
     * @test
     */
    public function returnsDefaultsWithoutForm()
    {
        $instance = $this->createInstance();
        $result = $this->callInaccessibleMethod($instance, 'getPreviewOptions');
        $this->assertEquals([
            PreviewOption::MODE => PreviewOption::MODE_APPEND,
            PreviewOption::TOGGLE => true,
        ], $result);
    }

    /**
     * @test
     */
    public function avoidsRenderPreviewSectionIfTemplateFileDoesNotExist()
    {
        $provider = $this->getMockBuilder(Provider::class)
            ->setMethods(['getTemplatePathAndFilename'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->expects($this->atLeastOnce())->method('getTemplatePathAndFilename')->willReturn(null);
        $previewView = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->callInaccessibleMethod($previewView, 'renderPreviewSection', $provider, []);
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instance = $this->getMockBuilder(PreviewView::class)
            ->setMethods(['configurePageLayoutViewForLanguageMode'])
            ->disableOriginalConstructor()
            ->getMock();
        $instance->expects($this->any())->method('configurePageLayoutViewForLanguageMode')->willReturnArgument(0);
        return $instance;
    }
}
