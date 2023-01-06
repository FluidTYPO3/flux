<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Integration\ViewBuilder;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\RenderingContextBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class ViewBuilderTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        $renderingContext = $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass();
        $renderingContextBuilder = $this->getMockBuilder(RenderingContextBuilder::class)
            ->setMethods(['buildRenderingContextFor'])
            ->disableOriginalConstructor()
            ->getMock();
        $renderingContextBuilder->method('buildRenderingContextFor')->willReturn($renderingContext);

        GeneralUtility::addInstance(RenderingContextBuilder::class, $renderingContextBuilder);

        parent::setUp();
    }

    public function testBuildTemplateView(): void
    {
        $view = $this->getMockBuilder(TemplateView::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(TemplateView::class, $view);

        $subject = new ViewBuilder();
        $view = $subject->buildTemplateView('FluidTYPO3.Flux', 'Default', 'default');
        self::assertInstanceOf(TemplateView::class, $view);
    }

    public function testBuildPreviewView(): void
    {
        $view = $this->getMockBuilder(PreviewView::class)->disableOriginalConstructor()->getMock();
        GeneralUtility::addInstance(PreviewView::class, $view);

        $subject = new ViewBuilder();
        $view = $subject->buildPreviewView('FluidTYPO3.Flux', 'Default', 'default');
        self::assertInstanceOf(PreviewView::class, $view);
    }
}
