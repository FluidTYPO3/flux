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
 *
 * @package Flux
 */
class AbstractFluxControllerTestCase extends AbstractTestCase {

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
	protected function assertSimpleActionCallsRenderOnView($action) {
		$instance = $this->objectManager->get(str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4)));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['render', 'assign']);
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		$view->expects($this->once())->method('render')->will($this->returnValue('rendered'));
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'response', $response, TRUE);
		ObjectAccess::setProperty($instance, 'actionMethodName', $action . 'Action', TRUE);
		$this->callInaccessibleMethod($instance, 'callActionMethod');
		$output = $response->getcontent();
		$this->assertEquals('rendered', $output);
	}

	/**
	 * @return string
	 */
	protected function getControllerName() {
		if (TRUE === strpos(get_class($this), '\\')) {
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
	public function canCreateInstanceOfCustomRegisteredController() {
		$instance = $this->createAndTestDummyControllerInstance();
		$this->assertInstanceOf('FluidTYPO3\Flux\Controller\AbstractFluxController', $instance);
		return $instance;
	}

	/**
	 * @return void
	 */
	protected function performDummyRegistration() {
		Core::registerProviderExtensionKey($this->extensionName, $this->getControllerName());
		$this->assertContains($this->extensionName, Core::getRegisteredProviderExtensionKeys($this->getControllerName()));
	}

	/**
	 * @return AbstractFluxController
	 */
	protected function createAndTestDummyControllerInstance() {
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		return $this->objectManager->get($controllerClassName);
	}

	/**
	 * @param string $controllerName
	 * @return array
	 */
	protected function createDummyRequestAndResponseForFluxController($controllerName = 'Content') {
		/** @var Request $request */
		$request = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Request');
		$request->setControllerExtensionName('Flux');
		$request->setControllerActionName($this->defaultAction);
		$request->setControllerName($controllerName);
		$request->setControllerObjectName(ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction($this->extensionName, $this->defaultAction, $controllerName));
		$request->setFormat('html');
		/** @var Response $response */
		$response = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Web\Response');
		return [$request, $response];
	}

	/**
	 * @test
	 */
	public function canGetData() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$data = $this->callInaccessibleMethod($instance, 'getData');
		$this->assertIsArray($data);
	}

	/**
	 * @test
	 */
	public function canGetSetup() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$setup = $this->callInaccessibleMethod($instance, 'getSetup');
		$this->assertIsArray($setup);
	}

	/**
	 * @test
	 */
	public function canGetRecord() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$record = $this->callInaccessibleMethod($instance, 'getRecord');
		$this->assertIsArray($record);
	}

	/**
	 * @test
	 */
	public function canGetFluxRecordField() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$field = $this->callInaccessibleMethod($instance, 'getFluxRecordField');
		$this->assertSame('pi_flexform', $field);
	}

	/**
	 * @test
	 */
	public function canGetFluxTableName() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$table = $this->callInaccessibleMethod($instance, 'getFluxTableName');
		$this->assertSame('tt_content', $table);
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithMatchingExtensionName() {
		$controllerName = $this->getControllerName();
		$controllerClassName = substr(get_class($this), 0, -4);
		$instance = $this->getMock($controllerClassName, ['hasSubControllerActionOnForeignController']);
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(FALSE));
		$viewContext = new ViewContext(NULL, $this->extensionName, $controllerName);
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canPerformSubRenderingWithNotMatchingExtensionName() {
		$controllerName = $this->getControllerName();
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['hasSubControllerActionOnForeignController', 'callSubControllerAction']);
		$instance->expects($this->once())->method('hasSubControllerActionOnForeignController')->will($this->returnValue(TRUE));
		$instance->expects($this->once())->method('callSubControllerAction');
		$instance->injectConfigurationService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
		$viewContext = new ViewContext(NULL, $this->extensionName, $controllerName);
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', $this->extensionName, TRUE);
		$this->callInaccessibleMethod($instance, 'performSubRendering', $this->extensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canInitializeView() {
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['dummy'], [], '', FALSE);
		ObjectAccess::setProperty($view, 'objectManager', $this->objectManager, TRUE);
		$provider = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', ['getTemplatePathAndFilename']);
		$instance = $this->getAccessibleMock($controllerClassName,
			['initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewVariables']);
		$fluxService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getPreparedExposedTemplateView']);
		$fluxService->expects($this->once())->method('getPreparedExposedTemplateView')->will($this->returnValue($view));
		$instance->_set('configurationManager', $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager'));
		$instance->_set('configurationService', $fluxService);
		$instance->_set('provider', $provider);
		$instance->_set('request', new Request());
		$instance->_set('controllerContext', new ControllerContext());
		$instance->expects($this->at(0))->method('initializeProvider');
		$instance->expects($this->at(1))->method('initializeSettings');
		$instance->expects($this->at(2))->method('initializeOverriddenSettings');
		$instance->expects($this->at(3))->method('initializeViewVariables');
		$instance->initializeView($view);
	}

	/**
	 * @test
	 */
	public function canInitializeViewWithTemplateSource() {
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['setTemplateSource'], [], '', FALSE);
		ObjectAccess::setProperty($view, 'objectManager', $this->objectManager, TRUE);
		$provider = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', ['getTemplateFilename', 'getTemplateSource']);
		$instance = $this->getAccessibleMock($controllerClassName,
			['initializeProvider', 'initializeSettings', 'initializeOverriddenSettings', 'initializeViewVariables']);
		$fluxService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getPreparedExposedTemplateView']);
		$fluxService->expects($this->once())->method('getPreparedExposedTemplateView')->will($this->returnValue($view));
		$instance->_set('configurationManager', $this->objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManager'));
		$instance->_set('configurationService', $fluxService);
		$instance->_set('provider', $provider);
		$instance->_set('request', new Request());
		$instance->_set('controllerContext', new ControllerContext());
		$instance->expects($this->at(0))->method('initializeProvider');
		$instance->expects($this->at(1))->method('initializeSettings');
		$instance->expects($this->at(2))->method('initializeOverriddenSettings');
		$instance->expects($this->at(3))->method('initializeViewVariables');
		$instance->initializeView($view);
	}

	/**
	 * @test
	 */
	public function canInitializeSettings() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['getRecord']);
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', ['getExtensionKey', 'getFlexFormValues', 'getTemplatePaths']);
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue($this->extensionKey));
		$provider->expects($this->once())->method('getFlexFormValues')->with($row)->will($this->returnValue([]));
		$provider->expects($this->once())->method('getTemplatePaths')->with($row)->will($this->returnValue([]));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', ['getPluginName']);
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'configurationManager', $this->objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManagerInterface'), TRUE);
		$this->callInaccessibleMethod($instance, 'initializeSettings');
	}

	/**
	 * @dataProvider getInitializeOverriddenSettingsTestValues
	 * @param array $data
	 * @param array $settings
	 */
	public function testInitializeOverriddenSettings(array $data, array $settings) {
		$record = ['uid' => 1];
		$provider = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', ['getExtensionKey']);
		$provider->expects($this->once())->method('getExtensionKey')->with($record);
		$mock = $this->getMockForAbstractClass(
			'FluidTYPO3\\Flux\\Controller\\AbstractFluxController', [], '', FALSE, FALSE, FALSE,
			['getRecord']
		);
		$mock->expects($this->once())->method('getRecord')->willReturn($record);
		$service = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getSettingsForExtensionName']);
		if (TRUE === (boolean) $data['settings']['useTypoScript'] || TRUE === (boolean) $settings['useTypoScript']) {
			$service->expects($this->once())->method('getSettingsForExtensionName');
		} else {
			$service->expects($this->never())->method('getSettingsForExtensionName');
		}
		$mock->injectConfigurationService($service);
		ObjectAccess::setProperty($mock, 'data', $data, TRUE);
		ObjectAccess::setProperty($mock, 'settings', $settings, TRUE);
		ObjectAccess::setProperty($mock, 'provider', $provider, TRUE);
		$this->callInaccessibleMethod($mock, 'initializeOverriddenSettings');
	}

	/**
	 * @return array
	 */
	public function getInitializeOverriddenSettingsTestValues() {
		return [
			[['settings' => []], [], []],
			[['settings' => []], ['useTypoScript' => 1]],
			[['settings' => ['useTypoScript' => 1]], []],
		];
	}

	/**
	 * @test
	 */
	public function testInitializeProvider() {
		$provider = new Provider();
		$service = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['resolvePrimaryConfigurationProvider']);
		$service->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
		$mock = $this->getMockForAbstractClass(
			'FluidTYPO3\\Flux\\Controller\\AbstractFluxController', [], '', FALSE, FALSE, FALSE,
			['getRecord', 'getFluxTableName', 'getFluxRecordField']
		);
		$mock->expects($this->once())->method('getRecord')->willReturn([]);
		$mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
		$mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');
		$mock->injectConfigurationService($service);
		$this->callInaccessibleMethod($mock, 'initializeProvider');
	}

	/**
	 * @test
	 */
	public function testInitializeProviderThrowsExceptionIfNoProviderResolved() {
		$service = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['resolvePrimaryConfigurationProvider']);
		$service->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn(NULL);
		$mock = $this->getMockForAbstractClass(
			'FluidTYPO3\\Flux\\Controller\\AbstractFluxController', [], '', FALSE, FALSE, FALSE,
			['getRecord', 'getFluxTableName', 'getFluxRecordField']
		);
		$mock->expects($this->once())->method('getRecord')->willReturn([]);
		$mock->expects($this->once())->method('getFluxTableName')->willReturn('table');
		$mock->expects($this->once())->method('getFluxRecordField')->willReturn('field');
		$mock->injectConfigurationService($service);
		$this->setExpectedException('RuntimeException');
		$this->callInaccessibleMethod($mock, 'initializeProvider');
	}

	/**
	 * @test
	 */
	public function canInitializeViewObject() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['getRecord']);
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', ['getExtensionKey', 'getTemplatePathAndFilename']);
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue($this->extensionKey));
		$provider->expects($this->once())->method('getTemplatePathAndFilename')->with($row)->will($this->returnValue('/dev/null'));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['dummy']);
		$configurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', ['getPreparedExposedTemplateView']);
		$configurationService->expects($this->once())->method('getPreparedExposedTemplateView')->will($this->returnValue($view));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', ['getControllerName']);
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Test'));
		$controllerContext = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ControllerContext');
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		ObjectAccess::setProperty($instance, 'controllerContext', $controllerContext, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewObject');
	}

	/**
	 * @test
	 */
	public function callingRenderActionExecutesExpectedMethodsOnNestedObjects() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['getRecord', 'performSubRendering']);
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$instance->expects($this->once())->method('performSubRendering')->with($this->extensionKey, 'Void', 'default', 'tx_flux_void')->will($this->returnValue('test'));
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', ['getExtensionKey', 'getControllerExtensionKeyFromRecord']);
		$provider->expects($this->once())->method('getExtensionKey')->with($row)->will($this->returnValue('flux'));
		$provider->expects($this->once())->method('getControllerExtensionKeyFromRecord')->with($row)->will($this->returnValue($this->extensionKey));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', ['getPluginName', 'getControllerName']);
		$request->expects($this->once())->method('getPluginName')->will($this->returnValue('void'));
		$request->expects($this->once())->method('getControllerName')->will($this->returnValue('Void'));
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		$result = $instance->renderAction();
		$this->assertEquals($result, 'test');
	}

	/**
	 * @test
	 */
	public function performSubRenderingCallsViewRenderOnNativeTarget() {
		$controllerName = $this->getControllerName();
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['callSubControllerAction']);
		$instance->expects($this->never())->method('callSubControllerAction');
		$instance->injectConfigurationService($this->objectManager->get('FluidTYPO3\\Flux\\Service\\FluxService'));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['render']);
		$view->expects($this->once())->method('render')->will($this->returnValue('test'));
		ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, TRUE);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'performSubRendering', $this->shortExtensionName, $controllerName, $this->defaultAction, 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function callingSubControllerActionExecutesExpectedMethodsOnNestedObjects() {
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$instance = $this->getMock($controllerClassName, ['processRequest']);
		$objectManager = $this->getMock(get_class($this->objectManager), ['get']);
		$responseClassName = 'TYPO3\CMS\Extbase\Mvc\Web\Response';
		$response = $this->getMock($responseClassName, ['getContent']);
		$response->expects($this->once())->method('getContent')->will($this->returnValue('test'));
		$objectManager->expects($this->at(0))->method('get')->with($responseClassName)->will($this->returnValue($response));
		$objectManager->expects($this->at(1))->method('get')->with($controllerClassName)->will($this->returnValue($instance));
		$request = $this->getMock('TYPO3\CMS\Extbase\Mvc\Web\Request', ['setControllerActionName', 'setArguments']);
		$request->expects($this->once())->method('setControllerActionName')->with($this->defaultAction);
		ObjectAccess::setProperty($instance, 'objectManager', $objectManager, TRUE);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$instance->expects($this->once())->method('processRequest')->with($request, $response);
		$result = $this->callInaccessibleMethod($instance, 'callSubControllerAction', $this->shortExtensionName, $controllerClassName, $this->defaultAction, 'tx_flux_content');
		$this->assertEquals('test', $result);
	}

	/**
	 * @test
	 */
	public function canInitializeViewVariables() {
		$controllerClassName = str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
		$data = ['test' => 'test'];
		$variables = ['foo' => 'bar'];
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$instance = $this->getMock($controllerClassName, ['getRecord']);
		$instance->expects($this->once())->method('getRecord')->will($this->returnValue($row));
		$view = $this->getMock('FluidTYPO3\Flux\View\ExposedTemplateView', ['assign', 'assignMultiple']);
		$provider = $this->getMock('FluidTYPO3\Flux\Provider\Provider', ['getTemplatePaths', 'getTemplateVariables']);
		$provider->expects($this->once())->method('getTemplateVariables')->with($row)->will($this->returnValue($variables));
		$view->expects($this->atLeastOnce())->method('assignMultiple');
		$view->expects($this->atLeastOnce())->method('assign');
		ObjectAccess::setProperty($instance, 'provider', $provider, TRUE);
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'data', $data, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeViewVariables');
	}

	/**
	 * @disabledtest
	 */
	public function canPerformSubRenderingWithForeignExtensionNameWhichContainsAlternativeController() {
		$controllerName = $this->getControllerName();
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		class_alias('FluidTYPO3\Flux\Controller\ContentController', 'FluidTYPO3\Other\Controller\ContentController');
		$viewContext = new ViewContext(NULL, 'FluidTYPO3.Other', $controllerName);
		$view = $this->createFluxServiceInstance()->getPreparedExposedTemplateView($viewContext);
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		ObjectAccess::setProperty($instance, 'view', $view, TRUE);
		ObjectAccess::setProperty($instance, 'extensionName', $this->shortExtensionName, TRUE);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$this->callInaccessibleMethod($instance, 'performSubRendering', 'Other', $controllerName, $this->defaultAction, 'tx_flux_content');
	}

	/**
	 * @test
	 */
	public function canUseTypoScriptSettingsInsteadOfFlexFormDataWhenRequested() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$settings = [
			'useTypoScript' => TRUE
		];
		$previousSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		ObjectAccess::setProperty($instance, 'settings', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertNotSame($previousSettings, $overriddenSettings);
	}

	/**
	 * @test
	 */
	public function canUseFlexFormDataWhenPresent() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$settings = [
			'settings' => [
				'test' => 'test'
			]
		];
		ObjectAccess::setProperty($instance, 'data', $settings, TRUE);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
		$this->callInaccessibleMethod($instance, 'initializeOverriddenSettings');
		$overriddenSettings = ObjectAccess::getProperty($instance, 'settings', TRUE);
		$this->assertEquals($settings['settings']['test'], $overriddenSettings['test']);
	}

	/**
	 * @disabledtest
	 */
	public function canCallSubControllerErrorAction() {
		list ($request, ) = $this->createDummyRequestAndResponseForFluxController();
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		$class = get_class($instance);
		ObjectAccess::setProperty($instance, 'request', $request, TRUE);
		$this->callInaccessibleMethod($instance, 'callSubControllerAction', $class, 'error', 'tx_flux_api');
	}

	/**
	 * @disabledtest
	 */
	public function throwsRuntimeExceptionWhenInitializingProviderAndNoneIsDetected() {
		$instance = $this->canCreateInstanceOfCustomRegisteredController();
		ObjectAccess::setProperty($instance, 'fluxTableName', 'void', TRUE);
		$this->setExpectedException('RuntimeException', NULL, 1377458581);
		$this->callInaccessibleMethod($instance, 'initializeProvider');
	}

	/**
	 * @disabledtest
	 */
	public function canExecuteBasicRequestUsingCustomController() {
		$controllerName = $this->getControllerName();
		list ($request, $response) = $this->createDummyRequestAndResponseForFluxController($controllerName);
		/** @var Dispatcher $dispatcher */
		$dispatcher = $this->objectManager->get('TYPO3\CMS\Extbase\Mvc\Dispatcher');
		$this->setExpectedException('TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException', NULL, 1257246929);
		$dispatcher->dispatch($request, $response);
	}

}
