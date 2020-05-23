<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ControllerPipe;
use FluidTYPO3\Development\ProtectedAccess;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument;

/**
 * ControllerPipeTest
 */
class ControllerPipeTest extends AbstractPipeTestCase
{

    /**
     * @var array
     */
    protected $defaultData = array(
        'action' => 'test',
        'controller' => 'test2',
        'extensionName' => 'test3'
    );

    /**
     * @test
     */
    public function canConductData()
    {
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $instance->setExtensionName('Flux');
        $instance->setController('Fake');
        $instance->setAction('render');
        $result = $this->performControllerExecution($instance, 'Tx_Flux_Controller_FakeController');
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function canConductDataWithVendorNamedController()
    {
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $instance->setExtensionName('FluidTYPO3.Flux');
        $instance->setController('Vendor');
        $instance->setAction('render');
        $result = $this->performControllerExecution($instance, 'Tx_Flux_Controller_VendorController');
        $this->assertNotEmpty($result);
    }

    /**
     * @param ControllerPipe $instance
     * @param string $controllerClassName
     * @return mixed
     */
    protected function performControllerExecution(ControllerPipe $instance, $controllerClassName)
    {
        $controllerMock = $this->getMockBuilder(
            'FluidTYPO3\Flux\Controller\AbstractFluxController'
        )->setMockClassName($controllerClassName)->setMethods(
            array(
                'renderAction', 'initializeActionMethodArguments', 'initializeActionMethodValidators', 'canProcessRequest', 'mapRequestArgumentsToControllerArguments',
                'checkRequestHash', 'buildControllerContext', 'setViewConfiguration', 'resolveView', 'getRecord', 'initializeProvider', 'initializeSettings',
                'initializeOverriddenSettings'
            )
        )->disableOriginalConstructor()->getMock();
        $controllerMock->expects($this->once())->method('initializeActionMethodArguments');
        $controllerMock->expects($this->once())->method('initializeActionMethodValidators');
        $controllerMock->expects($this->once())->method('renderAction')->will($this->returnValue($this->defaultData));
        $controllerMock->expects($this->once())->method('canProcessRequest')->will($this->returnValue(true));
        $signalSlotDispatcherMock = $this->getMockBuilder('TYPO3\CMS\Extbase\SignalSlot\Dispatcher')->disableOriginalConstructor()->setMethods(array('dispatch'))->getMock();
        $configurationManagerMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Configuration\ConfigurationManager')->disableOriginalConstructor()->setMethods(array('isFeatureEnabled'))->getMock();
        $configurationManagerMock->expects($this->any())->method('isFeatureEnabled')->will($this->returnValue(true));
        $propertyMappingServiceMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfigurationService')->setMethods(array('initializePropertyMappingConfigurationFromRequest'))->getMock();
        $argumentsMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Controller\Arguments')->setMethods(array('getIterator'))->getMock();
        $argumentsMock->expects($this->atLeastOnce())->method('getIterator')->will($this->returnValue(new \ArrayIterator(array(new Argument('test', 'string')))));
        ProtectedAccess::setProperty($controllerMock, 'objectManager', $this->objectManager);
        ProtectedAccess::setProperty($controllerMock, 'configurationManager', $configurationManagerMock);
        ProtectedAccess::setProperty($controllerMock, 'mvcPropertyMappingConfigurationService', $propertyMappingServiceMock);
        ProtectedAccess::setProperty($controllerMock, 'arguments', $argumentsMock);
        ProtectedAccess::setProperty($controllerMock, 'signalSlotDispatcher', $signalSlotDispatcherMock);
        $objectManagerMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Object\ObjectManager')->disableOriginalConstructor()->setMethods(array('get'))->getMock();
        $response = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Web\Response')->setMethods(array('getContent'))->getMock();
        $response->expects($this->once())->method('getContent')->will($this->returnValue($this->defaultData));
        $request = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Web\Request')->setMethods(array('getControllerActionName', 'getMethodParameters', 'getDispatched'))->getMock();
        $request->expects($this->at(0))->method('getDispatched')->will($this->returnValue(false));
        $request->expects($this->atLeastOnce())->method('getControllerActionName')->will($this->returnValue('render'));
        $dispatcherMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Dispatcher')->disableOriginalConstructor()->setMethods(array('resolveController'))->setConstructorArgs(array($objectManagerMock))->getMock();
        ProtectedAccess::setProperty($dispatcherMock, 'signalSlotDispatcher', $signalSlotDispatcherMock);
        ProtectedAccess::setProperty($dispatcherMock, 'objectManager', $this->objectManager);
        $dispatcherMock->expects($this->once())->method('resolveController')->will($this->returnValue($controllerMock));
        $objectManagerMock->expects($this->at(0))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Web\Request')->will($this->returnValue($request));
        $objectManagerMock->expects($this->at(1))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Web\Response')->will($this->returnValue($response));
        $objectManagerMock->expects($this->at(2))->method('get')->with('TYPO3\CMS\Extbase\Mvc\Dispatcher')->will($this->returnValue($dispatcherMock));
        ProtectedAccess::setProperty($instance, 'objectManager', $objectManagerMock);
        return $instance->conduct($this->defaultData);
    }

    /**
     * @test
     */
    public function canGetAndSetController()
    {
        $this->assertGetterAndSetterWorks('controller', 'Api', 'Api', true);
    }

    /**
     * @test
     */
    public function canGetAndSetAction()
    {
        $this->assertGetterAndSetterWorks('action', 'render', 'render', true);
    }

    /**
     * @test
     */
    public function canGetAndSetExtensionName()
    {
        $this->assertGetterAndSetterWorks('extensionName', 'FluidTYPO3.Flux', 'FluidTYPO3.Flux', true);
    }
}
