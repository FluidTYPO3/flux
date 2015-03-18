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

/**
 * Controller Action Outlet Pipe ViewHelper
 *
 * Adds a ControllerPipe to the Form's Outlet.
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class ControllerViewHelper extends AbstractPipeViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('action', 'string', 'Action to call on the controller, minus the "Action" suffix', TRUE);
		$this->registerArgument('controller', 'string', 'Class name of controller to call. If empty, uses current controller');
		$this->registerArgument('extensionName', 'string', 'Extension name of controller to call. If empty, uses current extension name');
	}

	/**
	 * @return PipeInterface
	 */
	protected function preparePipeInstance() {
		$extensionName = $this->arguments['extensionName'];
		$controller = $this->arguments['controller'];
		if (TRUE === empty($extensionName)) {
			$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
		}
		if (TRUE === empty($controller)) {
			$controller = $this->controllerContext->getRequest()->getControllerObjectName();
		}
		/** @var ControllerPipe $pipe */
		$pipe = $this->objectManager->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\ControllerPipe');
		$pipe->setAction($this->arguments['action']);
		$pipe->setController($controller);
		$pipe->setExtensionName($extensionName);
		return $pipe;
	}

}
