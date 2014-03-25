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
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ControllerPipeTest extends AbstractPipeTestCase {

	/**
	 * @test
	 */
	public function canConductData() {
		$instance = $this->createInstance();
		$instance->setExtensionName('Flux');
		$instance->setController('Fake');
		$instance->setAction('render');
		$this->performControllerExcecution($instance, 'Tx_Flux_Controller_FakeController');
	}

	/**
	 * @test
	 */
	public function canConductDataWithVendorNamedController() {
		$instance = $this->createInstance();
		$instance->setExtensionName('FluidTYPO3.Flux');
		$instance->setController('Vendor');
		$instance->setAction('render');
		$this->performControllerExcecution($instance, 'Tx_Flux_Controller_VendorController');
	}

	/**
	 * @param ControllerPipe $instance
	 * @param string $controllerClassName
	 * @return void
	 */
	protected function performControllerExcecution(ControllerPipe $instance, $controllerClassName) {
		$controllerMock = $this->getMockForAbstractClass('FluidTYPO3\Flux\Controller\AbstractFluxController', array(), $controllerClassName, TRUE, TRUE, TRUE,
			array(
				'renderAction', 'initializeActionMethodArguments', 'initializeActionMethodValidators', 'canProcessRequest', 'mapRequestArgumentsToControllerArguments',
				'checkRequestHash', 'buildControllerContext', 'setViewConfiguration', 'resolveView'
			));
		$controllerMock->expects($this->once())->method('initializeActionMethodArguments');
		$controllerMock->expects($this->once())->method('initializeActionMethodValidators');
		$controllerMock->expects($this->once())->method('renderAction')->will($this->returnValue($this->defaultData));
		$controllerMock->expects($this->once())->method('canProcessRequest')->will($this->returnValue(TRUE));
		$signalSlotDispatcherMock = $this->getMock('TYPO3\CMS\Extbase\SignalSlot\Dispatcher', array('dispatch'));
		$configurationManagerMock = $this->getMock('TYPO3\CMS\Extbase\Configuration\ConfigurationManager', array('isFeatureEnabled'));
		$configurationManagerMock->expects($this->atLeastOnce())->method('isFeatureEnabled')->will($this->returnValue(TRUE));
		$propertyMappingServiceMock = $this->getMock('TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService', array('initializePropertyMappingConfigurationFromRequest'));
		$argumentsMock = $this->getMock('TYPO3\CMS\Extbase\Mvc\Controller\Arguments', array('getIterator'));
		$argumentsMock->expects($this->atLeastOnce())->method('getIterator')->will($this->returnValue(new \ArrayIterator(array(new Argument('test', 'string')))));
		ObjectAccess::setProperty($controllerMock, 'objectManager', $this->objectManager, TRUE);
		ObjectAccess::setProperty($controllerMock, 'configurationManager', $configurationManagerMock, TRUE);
		ObjectAccess::setProperty($controllerMock, 'mvcPropertyMappingConfigurationService', $propertyMappingServiceMock, TRUE);
		ObjectAccess::setProperty($controllerMock, 'arguments', $argumentsMock, TRUE);
		ObjectAccess::setProperty($controllerMock, 'signalSlotDispatcher', $signalSlotDispatcherMock, TRUE);
		$objectManagerMock = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager', array('get'));
		$response = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Response', array('getContent'));
		$response->expects($this->once())->method('getContent')->will($this->returnValue($this->defaultData));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', array('getControllerActionName', 'getMethodParameters', 'getDispatched'));
		$request->expects($this->at(0))->method('getDispatched')->will($this->returnValue(FALSE));
		$request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('render'));
		$dispatcherMock = $this->getMock('TYPO3\CMS\Extbase\Mvc\Dispatcher', array('resolveController'), array($objectManagerMock));
		ObjectAccess::setProperty($dispatcherMock, 'signalSlotDispatcher', $signalSlotDispatcherMock, TRUE);
		ObjectAccess::setProperty($dispatcherMock, 'objectManager', $this->objectManager, TRUE);
		$dispatcherMock->expects($this->once())->method('resolveController')->will($this->returnValue($controllerMock));
		$objectManagerMock->expects($this->at(0))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Web\Request')->will($this->returnValue($request));
		$objectManagerMock->expects($this->at(1))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Web\Response')->will($this->returnValue($response));
		$objectManagerMock->expects($this->at(2))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Dispatcher')->will($this->returnValue($dispatcherMock));
		ObjectAccess::setProperty($instance, 'objectManager', $objectManagerMock, TRUE);
		$output = $instance->conduct($this->defaultData);
		$this->assertNotEmpty($output);

	}

	/**
	 * @test
	 */
	public function canGetAndSetController() {
		$this->assertGetterAndSetterWorks('controller', 'Api', 'Api', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetAction() {
		$this->assertGetterAndSetterWorks('action', 'render', 'render', TRUE);
	}

	/**
	 * @test
	 */
	public function canGetAndSetExtensionName() {
		$this->assertGetterAndSetterWorks('extensionName', 'FluidTYPO3.Flux', 'FluidTYPO3.Flux', TRUE);
	}

}
