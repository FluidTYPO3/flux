<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Dispatcher;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Response;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Test case for Flux-enabled controllers
 */
class AbstractFluxControllerTestCase extends AbstractTestCase
{

    /**
     * @var string
     */
    protected $extensionName = 'FluidTYPO3.Flux';

    /**
     * @var string
     */
    protected $defaultAction = 'render';

    /**
     * @var string
     */
    protected $extensionKey = 'flux';

    /**
     * @var string
     */
    protected $shortExtensionName = 'Flux';

    /**
     * @param string $action
     * @return void
     */
    protected function assertSimpleActionCallsRenderOnView($action)
    {
        $instance = $this->objectManager->get(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)));
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('render', 'assign'))->getMock();
        $response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
        $view->expects($this->once())->method('render')->will($this->returnValue('rendered'));
        ObjectAccess::setProperty($instance, 'view', $view, true);
        ObjectAccess::setProperty($instance, 'response', $response, true);
        ObjectAccess::setProperty($instance, 'actionMethodName', $action . 'Action', true);
        $this->callInaccessibleMethod($instance, 'callActionMethod');
        $output = $response->getcontent();
        $this->assertEquals('rendered', $output);
    }

    /**
     * @return string
     */
    protected function getControllerName()
    {
        if (true === strpos(get_class($this), '\\')) {
            $parts = explode('\\', get_class($this));
        } else {
            $parts = explode('_', get_class($this));
        }
        $name = substr(array_pop($parts), 0, -9);
        return $name;
    }

    /**
     * @test
     * @return AbstractFluxController
     */
    public function canCreateInstanceOfCustomRegisteredController()
    {
        $instance = $this->createAndTestDummyControllerInstance();
        $this->assertInstanceOf('FluidTYPO3\Flux\Controller\AbstractFluxController', $instance);
        return $instance;
    }

    /**
     * @return void
     */
    protected function performDummyRegistration()
    {
        Core::registerProviderExtensionKey($this->extensionName, $this->getControllerName());
        $this->assertContains($this->extensionName, Core::getRegisteredProviderExtensionKeys($this->getControllerName()));
    }

    /**
     * @return AbstractFluxController
     */
    protected function createAndTestDummyControllerInstance()
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        return $this->objectManager->get($controllerClassName);
    }

    /**
     * @param string $controllerName
     * @return array
     */
    protected function createDummyRequestAndResponseForFluxController($controllerName = 'Content')
    {
        /** @var Request $request */
        $request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
        $request->setControllerExtensionName('Flux');
        $request->setControllerActionName($this->defaultAction);
        $request->setControllerName($controllerName);
        $request->setControllerObjectName(ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction($this->extensionName, $this->defaultAction, $controllerName));
        $request->setFormat('html');
        /** @var Response $response */
        $response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
        return array($request, $response);
    }

    /**
     * @test
     */
    public function canGetData()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $data = $this->callInaccessibleMethod($instance, 'getData');
        $this->assertIsArray($data);
    }

    /**
     * @test
     */
    public function canGetSetup()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $setup = $this->callInaccessibleMethod($instance, 'getSetup');
        $this->assertIsArray($setup);
    }

    /**
     * @test
     */
    public function canGetRecord()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $record = $this->callInaccessibleMethod($instance, 'getRecord');
        $this->assertIsArray($record);
    }

    /**
     * @test
     */
    public function canGetFluxRecordField()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $field = $this->callInaccessibleMethod($instance, 'getFluxRecordField');
        $this->assertSame('pi_flexform', $field);
    }

    /**
     * @test
     */
    public function canGetFluxTableName()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $table = $this->callInaccessibleMethod($instance, 'getFluxTableName');
        $this->assertSame('tt_content', $table);
    }

    /**
     * @disabledtest
     */
    public function canPerformSubRenderingWithMatchingExtensionName()
    {
        $controllerName = $this->getControllerName();
        $controllerClassName = substr(get_class($this), 0, -4);
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('hasSubControllerActionOnForeignController'))->getMock();
        $instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(false));
        $viewContext = new ViewContext(null, $this->extensionName, $controllerName);
        $view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
        ObjectAccess::setProperty($instance, 'view', $view, true);
        ObjectAccess::setProperty($instance, 'extensionName', 'Flux', true);
        $this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', null, 1257246929);
        $this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
    }

    /**
     * @test
     */
    public function canPerformSubRenderingWithNotMatchingExtensionName()
    {
        $controllerName = $this->getControllerName();
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('hasSubControllerActionOnForeignController', 'callSubControllerAction'))->getMock();
        $instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(true));
        $instance->expects($this->once())->method('callSubControllerAction');
        $instance->injectConfigurationService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
        $viewContext = new ViewContext(null, $this->extensionName, $controllerName);
        $view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
        ObjectAccess::setProperty($instance, 'view', $view, true);
        ObjectAccess::setProperty($instance, 'extensionName', $this->extensionName, true);
        $this->callInaccessibleMethod($instance, 'performSubRendering', $this->extensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
    }

    /**
     * @test
     */
    public function canInitializeView()
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $instance = $this->getMockBuilder(
            $controllerClassName
        )->setMethods(
            array('initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewVariables', 'initializeViewObject', 'initializeViewHelperVariableContainer'
            )
        )->getMock();
        $instance->expects($this->at(0))->method('initializeProvider');
        $instance->expects($this->at(1))->method('initializeSettings');
        $instance->expects($this->at(2))->method('initializeOverriddenSettings');
        $instance->expects($this->at(3))->method('initializeViewVariables');
        $instance->expects($this->at(4))->method('initializeViewObject');
        $instance->expects($this->at(5))->method('initializeViewHelperVariableContainer');
        $instance->initializeView($view);
    }

    /**
     * @test
     */
    public function canInitializeViewWithTemplateSource()
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('setTemplateSource'))->disableOriginalConstructor()->getMock();
        $instance = $this->getMockBuilder(
            $controllerClassName
        )->setMethods(
            array('initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewVariables', 'initializeViewObject', 'initializeViewHelperVariableContainer')
        )->getMock();
        $instance->expects($this->at(0))->method('initializeProvider');
        $instance->expects($this->at(1))->method('initializeSettings');
        $instance->expects($this->at(2))->method('initializeOverriddenSettings');
        $instance->expects($this->at(3))->method('initializeViewVariables');
        $instance->expects($this->at(4))->method('initializeViewObject');
        $instance->expects($this->at(5))->method('initializeViewHelperVariableContainer');
        $instance->initializeView($view);
    }

    /**
     * @test
     */
    public function canInitializeSettings()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('getRecord'))->getMock();
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $provider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('getExtensionKey', 'getFlexFormValues', 'getTemplatePaths'))->getMock();
        $provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue($this->extensionKey));
        $provider->expects($this->once())->method('getFlexFormValues')->with($row)->will($this->returnValue(array()));
        $provider->expects($this->once())->method('getTemplatePaths')->with($row)->will($this->returnValue(array()));
        $request = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Web\Request')->setMethods(array('getPluginName'))->getMock();
        $request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
        ObjectAccess::setProperty($instance, 'request', $request, true);
        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        ObjectAccess::setProperty($instance, 'configurationManager', $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface'), true);
        $this->callInaccessibleMethod($instance, 'initializeSettings');
    }

    /**
     * @dataProvider getInitializeOverriddenSettingsTestValues
     * @param array $data
     * @param array $settings
     */
    public function testInitializeOverriddenSettings(array $data, array $settings)
    {
        $record = array('uid' => 1);
        $provider = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array('getExtensionKey'))->getMock();
        $provider->expects($this->once())->method('getExtensionKey')->with($record);
        $mock = $this->getMockBuilder(
            'FluidTYPO3\\Flux\\Controller\\AbstractFluxController'
        )->setMethods(
            array('getRecord')
        )->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn($record);
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('getSettingsForExtensionName'))->getMock();
        if (true === (boolean) $data['settings']['useTypoScript'] || true === (boolean) $settings['useTypoScript']) {
            $service->expects($this->once())->method('getSettingsForExtensionName');
        } else {
            $service->expects($this->never())->method('getSettingsForExtensionName');
        }
        $mock->injectConfigurationService($service);
        ObjectAccess::setProperty($mock, 'data', $data, true);
        ObjectAccess::setProperty($mock, 'settings', $settings, true);
        ObjectAccess::setProperty($mock, 'provider', $provider, true);
        $this->callInaccessibleMethod($mock, 'initializeOverriddenSettings');
    }

    /**
     * @return array
     */
    public function getInitializeOverriddenSettingsTestValues()
    {
        return array(
            array(array('settings' => array()), array(), array()),
            array(array('settings' => array()), array('useTypoScript' => 1)),
            array(array('settings' => array('useTypoScript' => 1)), array()),
        );
    }

    /**
     * @test
     */
    public function testInitializeProvider()
    {
        $provider = new Provider();
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolvePrimaryConfigurationProvider'))->getMock();
        $service->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $mock = $this->getMockBuilder(
            'FluidTYPO3\\Flux\\Controller\\AbstractFluxController'
        )->setMethods(
            array('getRecord', 'getFluxTableName', 'getFluxRecordField')
        )->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn(array());
        $mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
        $mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');
        $mock->injectConfigurationService($service);
        $this->callInaccessibleMethod($mock, 'initializeProvider');
    }

    /**
     * @test
     */
    public function testInitializeProviderThrowsExceptionIfNoProviderResolved()
    {
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolvePrimaryConfigurationProvider'))->getMock();
        $service->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn(null);
        $mock = $this->getMockBuilder(
            'FluidTYPO3\\Flux\\Controller\\AbstractFluxController'
        )->setMethods(
            array('getRecord', 'getFluxTableName', 'getFluxRecordField')
        )->disableOriginalConstructor()->getMock();
        $mock->expects($this->once())->method('getRecord')->willReturn(array());
        $mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
        $mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');
        $mock->injectConfigurationService($service);
        $this->setExpectedException('RuntimeException');
        $this->callInaccessibleMethod($mock, 'initializeProvider');
    }

    /**
     * @test
     */
    public function canInitializeViewObject()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('getRecord'))->getMock();
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $viewContext = new ViewContext('/dev/null', 'FluidTYPO3.Flux', 'Test');
        $provider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('getViewContext'))->getMock();
        $provider->expects($this->once())->method('getViewContext')->willReturn($viewContext);
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('dummy'))->getMock();
        $configurationService = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('getPreparedExposedTemplateView'))->getMock();
        $configurationService->expects($this->once())->method('getPreparedExposedTemplateView')->with($viewContext)->willReturn($view);
        ObjectAccess::setProperty($instance, 'request', $viewContext->getRequest(), true);
        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        ObjectAccess::setProperty($instance, 'configurationService', $configurationService, true);
        $this->callInaccessibleMethod($instance, 'initializeViewObject');
    }

    /**
     * @test
     */
    public function callingRenderActionExecutesExpectedMethodsOnNestedObjects()
    {
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('getRecord', 'performSubRendering'))->getMock();
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $instance->expects($this->once())->method('performSubRendering')->with($this->extensionKey, 'Void', 'default', 'tx_flux_void')->will($this->returnValue('test'));
        $provider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('getExtensionKey', 'getControllerExtensionKeyFromRecord'))->getMock();
        $provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
        $provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->with($row)->will($this->returnValue($this->extensionKey));
        $request = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Web\Request')->setMethods(array('getPluginName', 'getControllerName'))->getMock();
        $request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
        $request->expects($this->once())->method('getControllerName')->will($this->returnValue('Void'));
        ObjectAccess::setProperty($instance, 'request', $request, true);
        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        $result = $instance->renderAction();
        $this->assertEquals($result, 'test');
    }

    /**
     * @test
     */
    public function performSubRenderingCallsViewRenderOnNativeTarget()
    {
        $controllerName = $this->getControllerName();
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('callSubControllerAction'))->getMock();
        $instance->expects($this->never())->method('callSubControllerAction');
        $instance->injectConfigurationService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('render'))->getMock();
        $view->expects($this->once())->method('render')->will($this->returnValue('test'));
        ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, true);
        ObjectAccess::setProperty($instance, 'view', $view, true);
        $result = $this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
        $this->assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function callingSubControllerActionExecutesExpectedMethodsOnNestedObjects()
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('processRequest', 'getRecord', 'initializeViewHelperVariableContainer'))->getMock();
        $objectManager = $this->getMockBuilder(get_class($this->objectManager))->setMethods(array('get'))->getMock();
        $responseClassName = 'TYPO3\CMS\Extbase\Mvc\Web\Response';
        $response = $this->getMockBuilder($responseClassName)->setMethods(array('getContent'))->getMock();
        $response->expects($this->once())->method('getContent')->will($this->returnValue('test'));
        $objectManager->expects($this->at(0))->method('get')->with($responseClassName)->will($this->returnValue($response));
        $objectManager->expects($this->at(1))->method('get')->with($controllerClassName)->will($this->returnValue($instance));
        $request = $this->getMockBuilder('TYPO3\CMS\Extbase\Mvc\Web\Request')->setMethods(array('setControllerActionName'))->getMock();
        $request->expects($this->once())->method('setControllerActionName')->with('render');
        $provider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('getViewContext', 'getControllerActionFromRecord'))->getMock();
        $provider->expects($this->once())->method('getViewContext')->willReturn(new ViewContext(null, null, null, $request));
        $provider->expects($this->once())->method('getControllerActionFromRecord')->willReturn('render');
        ObjectAccess::setProperty($instance, 'objectManager', $objectManager, true);
        ObjectAccess::setProperty($instance, 'request', $request, true);
        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        $instance->expects($this->once())->method('processRequest')->with($request, $response);
        $instance->expects($this->once())->method('getRecord')->willReturn(array());
        $result = $this->callInaccessibleMethod($instance, 'callSubControllerAction', $this->shortExtensionName, $controllerClassName, 'render', 'tx_flux_content');
        $this->assertEquals('test', $result);
    }

    /**
     * @test
     */
    public function canInitializeViewVariables()
    {
        $controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
        $data = array('test' => 'test');
        $variables = array('foo' => 'bar');
        $row = Records::$contentRecordWithoutParentAndWithoutChildren;
        $instance = $this->getMockBuilder($controllerClassName)->setMethods(array('getRecord'))->getMock();
        $instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
        $view = $this->getMockBuilder('FluidTYPO3\Flux\View\ExposedTemplateView')->setMethods(array('assign', 'assignMultiple'))->getMock();
        $provider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('getTemplatePaths', 'getTemplateVariables', 'initializeViewHelperVariableContainer'))->getMock();
        $provider->expects($this->once())->method('getTemplateVariables')->with($row)->will($this->returnValue($variables));
        $view->expects($this->atLeastOnce())->method('assignMultiple');
        $view->expects($this->atLeastOnce())->method('assign');
        ObjectAccess::setProperty($instance, 'provider', $provider, true);
        ObjectAccess::setProperty($instance, 'view', $view, true);
        ObjectAccess::setProperty($instance, 'data', $data, true);
        $this->callInaccessibleMethod($instance, 'initializeViewVariables');
    }

    /**
     * @disabledtest
     */
    public function canPerformSubRenderingWithForeignExtensionNameWhichContainsAlternativeController()
    {
        $controllerName = $this->getControllerName();
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        class_alias('FluidTYPO3\Flux\Controller\ContentController', 'FluidTYPO3\Other\Controller\ContentController');
        $viewContext = new ViewContext(null, 'FluidTYPO3.Other', $controllerName);
        $view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
        list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
        ObjectAccess::setProperty($instance, 'view', $view, true);
        ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, true);
        ObjectAccess::setProperty($instance, 'request', $request, true);
        $this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', null, 1257246929);
        $this->callInaccessibleMethod($instance, 'performSubRendering', 'Other', $controllerName, $this->defaultAction, 'tx_flux_content');
    }

    /**
     * @test
     */
    public function canUseTypoScriptSettingsInsteadOfFlexFormDataWhenRequested()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $settings = array(
            'useTypoScript' => true
        );
        $previousSettings = ObjectAccess::getProperty($instance, 'settings', true);
        ObjectAccess::setProperty($instance, 'settings', $settings, true);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
        $this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
        $overriddenSettings = ObjectAccess::getProperty($instance, 'settings', true);
        $this->assertNotSame($previousSettings, $overriddenSettings);
    }

    /**
     * @test
     */
    public function canUseFlexFormDataWhenPresent()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $settings = array(
            'settings' => array(
                'test' => 'test'
            )
        );
        ObjectAccess::setProperty($instance, 'data', $settings, true);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
        $this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
        $overriddenSettings = ObjectAccess::getProperty($instance, 'settings', true);
        $this->assertEquals($settings['settings']['test'], $overriddenSettings['test']);
    }

    /**
     * @disabledtest
     */
    public function canCallSubControllerErrorAction()
    {
        list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        $class = get_class($instance);
        ObjectAccess::setProperty($instance, 'request', $request, true);
        $this->callInaccessibleMethod($instance, 'callSubControllerAction', $class, 'error', 'tx_flux_api');
    }

    /**
     * @disabledtest
     */
    public function throwsRuntimeExceptionWhenInitializingProviderAndNoneIsDetected()
    {
        $instance = $this->canCreateInstanceOfCustomRegisteredController();
        ObjectAccess::setProperty($instance, 'fluxTableName', 'void', true);
        $this->setExpectedException('RuntimeException', null, 1377458581);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
    }

    /**
     * @disabledtest
     */
    public function canExecuteBasicRequestUsingCustomController()
    {
        $controllerName = $this->getControllerName();
        list ($request, $response) = $this->createDummyRequestAndResponseForFluxController($controllerName);
        /** @var Dispatcher $dispatcher */
        $dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
        $this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', null, 1257246929);
        $dispatcher->dispatch($request, $response);
    }
}
