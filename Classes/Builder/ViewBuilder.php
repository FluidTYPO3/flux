<?php
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\PreviewView;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\VersionUtility;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

class ViewBuilder
{
    protected RenderingContextBuilder $renderingContextBuilder;
    protected RequestBuilder $requestBuilder;
    protected TypoScriptService $typoScriptService;

    public function __construct(
        RenderingContextBuilder $renderingContextBuilder,
        RequestBuilder $requestBuilder,
        TypoScriptService $typoScriptService
    ) {
        $this->renderingContextBuilder = $renderingContextBuilder;
        $this->requestBuilder = $requestBuilder;
        $this->typoScriptService = $typoScriptService;
    }

    public function buildPreviewView(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        string $pluginName,
        ?string $templatePathAndFilename = null
    ): PreviewView {
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

        /** @var ViewInterface&PreviewView $view */
        $view = GeneralUtility::makeInstance(PreviewView::class);
        if (method_exists($view, 'setRenderingContext')) {
            $view->setRenderingContext($renderingContext);
        }

        return $view;
    }

    /**
     * @param ServerRequestInterface|RequestInterface|null $request
     */
    public function buildTemplateView(
        string $extensionIdentity,
        string $controllerName,
        string $controllerAction,
        string $pluginName,
        ?string $templatePathAndFilename = null,
        ServerRequestInterface|RequestInterface|null $request = null
    ): ViewInterface {
        $renderingContext = $this->renderingContextBuilder->buildRenderingContextFor(
            $extensionIdentity,
            $controllerName,
            $controllerAction,
            $pluginName
        );

        return $this->createViewInstance(
            $extensionIdentity,
            $renderingContext,
            $templatePathAndFilename,
            $request
        );
    }

    public function buildTemplatePaths(string|array $extensionKeyOrConfiguration): TemplatePaths
    {
        /** @var TemplatePaths $paths */
        $paths = GeneralUtility::makeInstance(TemplatePaths::class);

        if (VersionUtility::isCoreAtLeast13()) {
            if (!is_array($extensionKeyOrConfiguration)) {
                $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKeyOrConfiguration);
                $resources = ExtensionManagementUtility::extPath($extensionKey) . 'Resources/Private/';
                $extensionKeyOrConfiguration = [
                    TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [$resources . 'Templates/'],
                    TemplatePaths::CONFIG_PARTIALROOTPATHS => [$resources . 'Partials/'],
                    TemplatePaths::CONFIG_LAYOUTROOTPATHS => [$resources . 'Layouts/'],
                ];
            }
        }

        if (is_array($extensionKeyOrConfiguration)) {
            $paths->setTemplateRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_TEMPLATEROOTPATHS]);
            $paths->setLayoutRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_LAYOUTROOTPATHS]);
            $paths->setPartialRootPaths($extensionKeyOrConfiguration[TemplatePaths::CONFIG_PARTIALROOTPATHS]);
        } else {
            $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionKeyOrConfiguration);
            try {
                $paths->setTemplateRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_TEMPLATES_DIRECTORY)
                );
                $paths->setLayoutRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_LAYOUTS_DIRECTORY)
                );
                $paths->setPartialRootPaths(
                    $this->createFluidPathSet($extensionKey, TemplatePaths::DEFAULT_PARTIALS_DIRECTORY)
                );
            } catch (\RuntimeException $exception) {
                if ($exception->getCode() !== 1700841298) {
                    throw $exception;
                }
            }
        }

        return $paths;
    }

    protected function createViewInstance(
        string $extensionIdentity,
        RenderingContextInterface $renderingContext,
        ?string $templatePathAndFilename,
        ServerRequestInterface|RequestInterface|null $request = null
    ): ViewInterface {
        $typoScriptViewConfiguration = null;
        $extensionSignature = ExtensionNamingUtility::getExtensionSignature($extensionIdentity);
        if ($request instanceof ServerRequestInterface
            && ($typoScript = $request->getAttribute('frontend.typoscript')) instanceof FrontendTypoScript
        ) {
            $typoScriptViewConfiguration = GeneralUtility::removeDotsFromTS(
                $typoScript->getSetupArray()['plugin.']['tx_' . $extensionSignature . '.']['view.'] ?? []
            );
        } else {
            $typoScriptViewConfiguration = (array) $this->typoScriptService->getTypoScriptByPath(
                'plugin.tx_' . $extensionSignature . '.view'
            );
        }

        if (!empty($typoScriptViewConfiguration)) {
            $templatePaths = $this->buildTemplatePaths($typoScriptViewConfiguration);
        } else {
            $templatePaths = $this->buildTemplatePaths($extensionIdentity);
        }

        if (VersionUtility::isCoreAtLeast13()) {
            $viewFactoryData = GeneralUtility::makeInstance(
                ViewFactoryData::class,
                $templatePaths->getTemplateRootPaths(),
                $templatePaths->getPartialRootPaths(),
                $templatePaths->getLayoutRootPaths(),
                $templatePathAndFilename,
                $request
            );
            /** @var ViewFactoryInterface $factory */
            $factory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
            /** @var ViewInterface $view */
            $view = $factory->create($viewFactoryData);
        } else {
            if ($templatePathAndFilename) {
                $templatePaths->setTemplatePathAndFilename($templatePathAndFilename);
            }
            $renderingContext->setTemplatePaths($templatePaths);

            if ($request && method_exists($renderingContext, 'setRequest')) {
                $renderingContext->setRequest($request);
            }

            /** @var ViewInterface $view */
            $view = GeneralUtility::makeInstance(TemplateView::class);
            if (method_exists($view, 'setRenderingContext')) {
                $view->setRenderingContext($renderingContext);
            }
        }

        return $view;
    }
    
    private function createFluidPathSet(string $extensionKey, string $subPath): array
    {
        return [
            'EXT:flux/' . $subPath,
            'EXT:' . $extensionKey . '/' . $subPath,
        ];
    }
}
