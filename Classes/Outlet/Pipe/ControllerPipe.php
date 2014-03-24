<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Pipe: Controller Action
 *
 * Passes data through a controller action
 *
 * @package Flux
 * @subpackage Outlet\Pipe
 */
class ControllerPipe extends AbstractPipe implements PipeInterface {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var string
	 */
	protected $controller;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var string
	 */
	protected $extensionName;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$fields = parent::getFormFields();
		$extensionNames = array_keys((array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']);
		$extensionNames = array_combine($extensionNames, $extensionNames);
		$fields['extensionName'] = Select::create(array('type' => 'Select'))
			->setName('extensionName')
			->setItems($extensionNames);
		$fields['controller'] = Input::create(array('type' => 'Input'))
			->setName('controller')
			->setValidate('trim,required');
		$fields['action'] = Input::create(array('type' => 'Input'))
			->setName('action')
			->setValidate('trim,required');
		return $fields;
	}

	/**
	 * @param string $controller
	 * @return ControllerPipe
	 */
	public function setController($controller) {
		$this->controller = $controller;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getController() {
		return $this->controller;
	}

	/**
	 * @param string $action
	 * @return ControllerPipe
	 */
	public function setAction($action) {
		$this->action = $action;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction() {
		return $this->action;
	}

	/**
	 * @param string $extensionName
	 * @return ControllerPipe
	 */
	public function setExtensionName($extensionName) {
		$this->extensionName = $extensionName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

	/**
	 * @param array $data
	 * @return mixed
	 */
	public function conduct($data) {
 		$extensionName = $this->getExtensionName();
		/** @var $request Request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		$request->setControllerName($this->getController());
		$request->setControllerActionName($this->getAction());
		list($vendorName, $extensionName) = ExtensionNamingUtility::getVendorNameAndExtensionName($extensionName);
		$request->setControllerExtensionName($extensionName);
		if (NULL !== $vendorName) {
			$request->setControllerVendorName($vendorName);
		}

		$request->setArguments($data);
		/** @var $response Response */
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		/** @var $dispatcher Dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
		$dispatcher->dispatch($request, $response);
		return $response->getContent();
	}

}
