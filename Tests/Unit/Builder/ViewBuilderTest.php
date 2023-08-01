<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Builder\ViewBuilder;
use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ViewBuilderTest extends AbstractTestCase
{
    protected RenderingContextBuilder $renderingContextBuilder;

    protected function setUp(): void
    {
        $renderingContext = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $this->renderingContextBuilder = $this->getMockBuilder(RenderingContextBuilder::class)
            ->setMethods(['buildRenderingContextFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->renderingContextBuilder->method('buildRenderingContextFor')->willReturn($renderingContext);

        parent::setUp();
    }

    public function testBuildTemplateView(): void
    {
        $view = $this->getMockBuilder(TemplateView::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(TemplateView::class, $view);

        $subject = new ViewBuilder($this->renderingContextBuilder);
        $view = $subject->buildTemplateView('FluidTYPO3.Flux', 'Default', 'default', 'defaut');
        self::assertInstanceOf(TemplateView::class, $view);
    }

    public function testBuildPreviewView(): void
    {
        $view = $this->getMockBuilder(PreviewView::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(PreviewView::class, $view);

        $subject = new ViewBuilder($this->renderingContextBuilder);
        $view = $subject->buildPreviewView('FluidTYPO3.Flux', 'Default', 'default', 'default');
        self::assertInstanceOf(PreviewView::class, $view);
    }
}
