<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\RenderingContextBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewBuilder
{
    public function buildPreviewView(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        ?string $templatePathAndFilename = null
    ): PreviewView {
        /** @var class-string $viewClassName */
        $viewClassName = PreviewView::class;

        $renderingContext = $this->buildRenderingContext(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
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
        ?string $templatePathAndFilename = null
    ): ViewInterface {
        /** @var class-string $viewClassName */
        $viewClassName = TemplateView::class;

        $renderingContext = $this->buildRenderingContext(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
            $templatePathAndFilename
        );

        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance($viewClassName);
        $view->setRenderingContext($renderingContext);
        return $view;
    }

    private function buildRenderingContext(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        ?string $templatePathAndFilename
    ): RenderingContextInterface {
        /** @var RenderingContextBuilder $renderingContextBuilder */
        $renderingContextBuilder = GeneralUtility::makeInstance(RenderingContextBuilder::class);
        return $renderingContextBuilder->buildRenderingContextFor(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
            $templatePathAndFilename
        );
    }
}
