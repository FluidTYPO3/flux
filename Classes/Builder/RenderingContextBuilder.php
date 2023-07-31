<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\ConfigurationContext;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\Service\ExtensionService;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\View\TemplatePaths;

class RenderingContextBuilder implements SingletonInterface
{
    private ConfigurationContext $context;
    private RequestBuilder $requestBuilder;

    /** @var array<string, RenderingContextInterface> */
    private array $templates = [];

    public function __construct(ConfigurationContext $context, RequestBuilder $requestBuilder)
    {
        $this->context = $context;
        $this->requestBuilder = $requestBuilder;
    }

    public function buildRenderingContextFor(
        string $extensionIdentity,
        string $controllerName,
        string $controllerActionName,
        ?string $templatePathAndFilename = null
    ): RenderingContextInterface {
        $renderingContext = clone $this->retrieveOrCreateTemplateRenderingContext($extensionIdentity, $controllerName);

        if (method_exists($renderingContext, 'setControllerAction')) {
            $renderingContext->setControllerAction($controllerActionName);
        }

        if ($templatePathAndFilename) {
            $templatePaths = clone $renderingContext->getTemplatePaths();
            $templatePaths->setTemplatePathAndFilename($templatePathAndFilename);
            $renderingContext->setTemplatePaths($templatePaths);
        }
        return $renderingContext;
    }

    private function retrieveOrCreateTemplateRenderingContext(
        string $extensionIdentity,
        string $controllerName
    ): RenderingContextInterface {
        $identifier = $extensionIdentity . '__' . $controllerName;
        if (isset($this->templates[$identifier])) {
            return $this->templates[$identifier];
        }

        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionIdentity);

        $renderingContext = $this->createRenderingContextInstance();

        /** @var RequestInterface&Request $request */
        $request = $this->requestBuilder->buildRequestFor($extensionIdentity, $controllerName, 'void', 'void');

        if (method_exists($renderingContext, 'setControllerContext')) {
            /** @var ControllerContext $controllerContext */
            $controllerContext = $this->buildControllerContext($request);
            try {
                $renderingContext->setControllerContext($controllerContext);
            } catch (\TypeError $error) {
                throw new \UnexpectedValueException(
                    'Controller class ' . $request->getControllerObjectName() . ' caused error: ' . $error->getMessage()
                );
            }
        } elseif (method_exists($renderingContext, 'setRequest')) {
            $renderingContext->setRequest($request);
        }

        if (!$this->context->isBootMode()) {
            $templatePaths = $renderingContext->getTemplatePaths();
        } else {
            $resources = ExtensionManagementUtility::extPath($extensionKey) . 'Resources/Private/';
            $paths = [
                TemplatePaths::CONFIG_TEMPLATEROOTPATHS => [$resources . 'Templates/'],
                TemplatePaths::CONFIG_PARTIALROOTPATHS => [$resources . 'Partials/'],
                TemplatePaths::CONFIG_LAYOUTROOTPATHS => [$resources . 'Layouts/'],
            ];
            /** @var TemplatePaths $templatePaths */
            $templatePaths = GeneralUtility::makeInstance(TemplatePaths::class, $paths);
        }

        $templatePaths->fillDefaultsByPackageName($extensionKey);
        $renderingContext->setTemplatePaths($templatePaths);

        if (method_exists($renderingContext, 'setControllerName')) {
            $renderingContext->setControllerName($controllerName);
        }

        $this->templates[$identifier] = $renderingContext;

        return $renderingContext;
    }

    /**
     * @codeCoverageIgnore
     */
    private function createRenderingContextInstance(): RenderingContextInterface
    {
        if (class_exists(RenderingContextFactory::class)) {
            /** @var RenderingContextFactory $renderingContextFactory */
            $renderingContextFactory = GeneralUtility::makeInstance(RenderingContextFactory::class);
            /** @var RenderingContext $renderingContext */
            $renderingContext = $renderingContextFactory->create();
        } else {
            /** @var RenderingContext $renderingContext */
            $renderingContext = GeneralUtility::makeInstance(RenderingContext::class);
        }
        return $renderingContext;
    }

    private function buildControllerContext(RequestInterface $request): ?ControllerContext
    {
        /** @var RequestInterface&Request $request */
        if (class_exists(ControllerContext::class)) {
            /** @var UriBuilder $uriBuilder */
            $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
            $uriBuilder->setRequest($request);
            if (method_exists($uriBuilder, 'injectEnvironmentService') && class_exists(EnvironmentService::class)) {
                /** @var EnvironmentService $environmentService */
                $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
                $uriBuilder->injectEnvironmentService($environmentService);
            }
            if (method_exists($uriBuilder, 'injectExtensionService')) {
                /** @var ExtensionService $extensionService */
                $extensionService = GeneralUtility::makeInstance(ExtensionService::class);
                $uriBuilder->injectExtensionService($extensionService);
            }
            if (method_exists($uriBuilder, 'injectConfigurationManager')) {
                /** @var ConfigurationManagerInterface $configurationManager */
                $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
                $uriBuilder->injectConfigurationManager($configurationManager);
            }

            if (method_exists($uriBuilder, 'initializeObject')) {
                $uriBuilder->initializeObject();
            }

            /** @var ControllerContext $controllerContext */
            $controllerContext = GeneralUtility::makeInstance(ControllerContext::class);
            $controllerContext->setRequest($request);
            $controllerContext->setUriBuilder($uriBuilder);
        }

        return $controllerContext ?? null;
    }
}
