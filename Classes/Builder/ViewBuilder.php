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
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\TemplatePaths;
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
            $pluginName
        );

        $templatePaths = $this->buildTemplatePaths($extensionIdentity);
        if ($templatePathAndFilename) {
            $templatePaths->setTemplatePathAndFilename($templatePathAndFilename);
        }
        $renderingContext->setTemplatePaths($templatePaths);

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
            $pluginName
        );

        $templatePaths = $this->buildTemplatePaths($extensionIdentity);
        if ($templatePathAndFilename) {
            $templatePaths->setTemplatePathAndFilename($templatePathAndFilename);
        }
        $renderingContext->setTemplatePaths($templatePaths);

        /** @var TemplateView $view */
        $view = GeneralUtility::makeInstance($viewClassName);
        $view->setRenderingContext($renderingContext);
        return $view;
    }

    /**
     * @param string|array $extensionKeyOrConfiguration
     * @codeCoverageIgnore
     */
    public function buildTemplatePaths($extensionKeyOrConfiguration): TemplatePaths
    {
        /** @var TemplatePaths $paths */
        $paths = GeneralUtility::makeInstance(TemplatePaths::class);

        if (is_array($extensionKeyOrConfiguration)) {
            $paths->setTemplateRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_TEMPLATEROOTPATHS]);
            $paths->setLayoutRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_LAYOUTROOTPATHS]);
            $paths->setPartialRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_PARTIALROOTPATHS]);
        } else {
            $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKeyOrConfiguration);
            try {
                $paths->fillDefaultsByPackageName($extensionKey);
            } catch (\RuntimeException $exception) {
                if ($exception->getCode() !== 1700841298) {
                    throw $exception;
                }
                $paths->setTemplateRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_TEMPLATES_DIRECTORY)
                );
                $paths->setLayoutRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_LAYOUTS_DIRECTORY)
                );
                $paths->setPartialRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_PARTIALS_DIRECTORY)
                );
            }
        }

        return $paths;
    }

    private function createFluidPathSet(string $extensionKey, string $subPath): array
    {
        return [
            'EXT:flux/' . $subPath,
            'EXT:' . $extensionKey . '/' . $subPath,
        ];
    }
}
