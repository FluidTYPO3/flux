<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\RequestBuilder;
use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewBuilderTest extends AbstractTestCase
{
    protected RenderingContextBuilder $renderingContextBuilder;
    protected RequestBuilder $requestBuilder;
    protected TypoScriptService $typoScriptService;

    protected function setUp(): void
    {
        $renderingContext = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $this->renderingContextBuilder = $this->getMockBuilder(RenderingContextBuilder::class)
            ->setMethods(['buildRenderingContextFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->typoScriptService = $this->getMockBuilder(TypoScriptService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContextBuilder->method('buildRenderingContextFor')->willReturn($renderingContext);
        $this->typoScriptService->method('getTypoScriptByPath')->willReturn(null);

        parent::setUp();
    }

    public function testBuildTemplateView(): void
    {
        $subject = $this->getMockBuilder(ViewBuilder::class)
            ->onlyMethods(['buildTemplatePaths', 'createViewInstance'])
            ->setConstructorArgs([$this->renderingContextBuilder, $this->requestBuilder, $this->typoScriptService])
            ->getMock();
        $subject->method('buildTemplatePaths')->willReturn(new TemplatePaths());
        $subject->method('createViewInstance')->willReturn(
            $this->getMockBuilder(ViewInterface::class)->getMock()
        );

        $view = $subject->buildTemplateView('FluidTYPO3.Flux', 'Default', 'default', 'defaut');
        self::assertInstanceOf(ViewInterface::class, $view);
    }

    public function testBuildPreviewView(): void
    {
        $singletons = GeneralUtility::getSingletonInstances();
        $configManager = $this->getMockBuilder(ConfigurationManager::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::setSingletonInstance(ConfigurationManager::class, $configManager);

        $previewView = $this->getMockBuilder(PreviewView::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(PreviewView::class, $previewView);

        $subject = $this->getMockBuilder(ViewBuilder::class)
            ->onlyMethods(['buildTemplatePaths', 'createViewInstance'])
            ->setConstructorArgs([$this->renderingContextBuilder, $this->requestBuilder, $this->typoScriptService])
            ->getMock();
        $subject->method('buildTemplatePaths')->willReturn(new TemplatePaths());
        $subject->method('createViewInstance')->willReturn(
            $this->getMockBuilder(PreviewView::class)->disableOriginalConstructor()->getMockForAbstractClass()
        );

        $view = $subject->buildPreviewView('FluidTYPO3.Flux', 'Default', 'default', 'default');
        self::assertInstanceOf(PreviewView::class, $view);
        GeneralUtility::resetSingletonInstances($singletons);
    }
}
