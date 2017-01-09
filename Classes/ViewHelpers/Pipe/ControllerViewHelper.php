<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ControllerPipe;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Controller Action Outlet Pipe ViewHelper
 *
 * Adds a ControllerPipe to the Form's Outlet.
 */
class ControllerViewHelper extends AbstractPipeViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
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

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return PipeInterface
     */
    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure = null
    ) {
        $extensionName = $arguments['extensionName'];
        $controller = $arguments['controller'];
        $controllerContext = $renderingContext->getControllerContext();
        if (true === empty($extensionName)) {
            $extensionName = $controllerContext->getRequest()->getControllerExtensionName();
        }
        if (true === empty($controller)) {
            $controller = $controllerContext->getRequest()->getControllerObjectName();
        }
        /** @var ControllerPipe $pipe */
        $pipe = GeneralUtility::makeInstance(ObjectManager::class)->get(ControllerPipe::class);
        $pipe->setAction($arguments['action']);
        $pipe->setController($controller);
        $pipe->setExtensionName($extensionName);
        return $pipe;
    }
}
