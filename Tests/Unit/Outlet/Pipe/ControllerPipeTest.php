<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ControllerPipe;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * ControllerPipeTest
 */
class ControllerPipeTest extends AbstractPipeTestCase
{
    /**
     * @var Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var array
     */
    protected $defaultData = array(
        'action' => 'test',
        'controller' => 'test2',
        'extensionName' => 'test3'
    );

    protected function setUp(): void
    {
        $this->response = $this->getMockBuilder(Response::class)->setMethods(['getContent'])->getMock();
    }

    protected function createInstance()
    {
        /** @var ControllerPipe $instance */
        $instance = parent::createInstance();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->method('get')->willReturnMap(
            [
                [Request::class, new Request()],
                [Response::class, $this->response],
                [Dispatcher::class, $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->getMock()],
            ]
        );
        $instance->injectObjectManager($objectManager);
        return $instance;
    }

    /**
     * @test
     */
    public function canConductData()
    {
        $this->response->method('getContent')->willReturn('foobar');
        $instance = $this->createInstance();
        $instance->setExtensionName('Flux');
        $instance->setController('Fake');
        $instance->setAction('render');
        $result = $this->performControllerExcecution($instance);
        $this->assertNotEmpty($result);
    }

    /**
     * @test
     */
    public function canConductDataWithVendorNamedController()
    {
        $this->response->method('getContent')->willReturn('foobar');
        $instance = $this->createInstance();
        $instance->setExtensionName('FluidTYPO3.Flux');
        $instance->setController('Vendor');
        $instance->setAction('render');
        $result = $this->performControllerExcecution($instance);
        $this->assertNotEmpty($result);
    }

    /**
     * @param ControllerPipe $instance
     * @param string $controllerClassName
     * @return mixed
     */
    protected function performControllerExcecution(ControllerPipe $instance)
    {
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
