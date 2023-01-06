<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ControllerPipe;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Controller Action Outlet Pipe ViewHelper
 *
 * Adds a ControllerPipe to the Form's Outlet.
 */
class ControllerViewHelper extends AbstractPipeViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'action',
            'string',
            'Action to call on the controller, minus the "Action" suffix',
            true
        );
        $this->registerArgument(
            'controller',
            'string',
            'Class name of controller to call. If empty, uses current controller'
        );
        $this->registerArgument(
            'extensionName',
            'string',
            'Extension name of controller to call. If empty, uses current extension name'
        );
    }

    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        ?\Closure $renderChildrenClosure = null
    ): ControllerPipe {
        /** @var array $arguments */
        $extensionName = $arguments['extensionName'];
        $controller = $arguments['controller'];
        $request = null;
        if (method_exists($renderingContext, 'getControllerContext')) {
            $controllerContext = $renderingContext->getControllerContext();
            $request = $controllerContext->getRequest();
        } elseif (method_exists($renderingContext, 'getRequest')) {
            $request = $renderingContext->getRequest();
        }

        if (empty($extensionName)) {
            $extensionName = $request->getControllerExtensionName();
        }
        if (empty($controller)) {
            $controller = $request->getControllerObjectName();
        }
        /** @var ControllerPipe $pipe */
        $pipe = GeneralUtility::makeInstance(ControllerPipe::class);
        $pipe->setAction((string) $arguments['action']);
        $pipe->setController((string) $controller);
        $pipe->setExtensionName((string) $extensionName);
        return $pipe;
    }
}
