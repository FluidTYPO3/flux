<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ControllerPipe;
use FluidTYPO3\Flux\Utility\RequestBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Response;

/**
 * ControllerPipeTest
 */
class ControllerPipeTest extends AbstractPipeTestCase
{
    /**
     * @var Response|MockObject
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
        $dispatcher = $this->getMockBuilder(Dispatcher::class)->disableOriginalConstructor()->getMock();
        if (is_a(Dispatcher::class, SingletonInterface::class, true)) {
            $this->singletonInstances[Dispatcher::class] = $dispatcher;
        } else {
            GeneralUtility::addInstance(Dispatcher::class, $dispatcher);
        }
        GeneralUtility::addInstance(
            Request::class,
            $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock()
        );
        GeneralUtility::addInstance(Response::class, $this->response);

        $requestBuilder = $this->getMockBuilder(RequestBuilder::class)
            ->setMethods(['getEnvironmentVariable'])
            ->getMock();
        $requestBuilder->method('getEnvironmentVariable')->willReturn('env');

        GeneralUtility::addInstance(RequestBuilder::class, $requestBuilder);

        parent::setUp();
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
