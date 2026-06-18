<?php
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class RenderingContextBuilder implements SingletonInterface
{
    public function buildRenderingContextFor(
        string $extensionIdentity,
        string $controllerName,
        string $controllerActionName,
        string $pluginName
    ): RenderingContextInterface {
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionIdentity);

        $renderingContext = $this->createRenderingContextInstance();

        if (method_exists($renderingContext, 'setControllerAction')) {
            $renderingContext->setControllerAction($controllerActionName);
        }
        if (method_exists($renderingContext, 'setControllerName')) {
            $renderingContext->setControllerName($controllerName);
        }

        return $renderingContext;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createRenderingContextInstance(): RenderingContextInterface
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
}
