<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewBuilder
{
    protected RenderingContextBuilder $renderingContextBuilder;

    public function __construct(RenderingContextBuilder $renderingContextBuilder)
    {
        $this->renderingContextBuilder = $renderingContextBuilder;
    }

    public function buildPreviewView(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        string $pluginName,
        ?string $templatePathAndFilename = null
    ): PreviewView {
        /** @var class-string $viewClassName */
        $viewClassName = PreviewView::class;

        $renderingContext = $this->renderingContextBuilder->buildRenderingContextFor(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
            $pluginName,
            $templatePathAndFilename
        );

        /** @var PreviewView $view */
        $view = GeneralUtility::makeInstance($viewClassName);
        $view->setRenderingContext($renderingContext);
        return $view;
    }

    public function buildTemplateView(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        string $pluginName,
        ?string $templatePathAndFilename = null
    ): ViewInterface {
        /** @var class-string $viewClassName */
        $viewClassName = TemplateView::class;

        $renderingContext = $this->renderingContextBuilder->buildRenderingContextFor(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
            $pluginName,
            $templatePathAndFilename
        );

        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance($viewClassName);
        $view->setRenderingContext($renderingContext);
        return $view;
    }
}
