<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Form_Field_ControllerActionsTest extends Tx_Flux_Tests_Functional_Form_Field_AbstractFieldTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'label' => 'Test field',
		'enable' => TRUE,
		'extensionName' => 'Flux',
		'pluginName' => 'API',
		'controllerName' => 'Flux',
		'actions' => array(),
		'disableLocalLanguageLabels' => FALSE,
		'excludeActions' => array(),
		'localLanguageFileRelativePath' => '/Resources/Private/Language/locallang.xml',
		'prefixOnRequiredArguments' => '*',
		'subActions' => array()
	);

	/**
	 * @test
	 */
	public function canRemoveVendorPrefixFromExtensionName() {
		$component = $this->createInstance();
		$keys = array(
			// actual => expected
			'Void.Fake' => 'Fake',
			'Void' => 'Void',
			'void' => 'void'
		);
		foreach ($keys as $actual => $expected) {
			$this->assertEquals($expected, $this->callInaccessibleMethod($component, 'removeVendorPrefixFromExtensionName', $actual));
		}
	}

	/**
	 * @test
	 */
	public function canUseRawItems() {
		$component = $this->createInstance();
		$items = array(
			array('foo' => 'Foo'),
			array('bar' => 'Bar')
		);
		$component->setItems($items);
		$this->assertSame($items, $component->getItems());
	}

	/**
	 * @test
	 */
	public function canSetAndGetSeparator() {
		$component = $this->createInstance();
		$separator = ' :: ';
		$component->setSeparator($separator);
		$this->assertSame($separator, $component->getSeparator());
	}

	/**
	 * @test
	 */
	public function acceptsNamespacedClasses() {
		$expectedClassName = 'FluidTYPO3\\Flux\\Controller\\ContentController';
		class_alias('Tx_Flux_Controller_ContentController', $expectedClassName);
		$component = $this->createInstance();
		$component->setExtensionName('FluidTYPO3.Flux');
		$className = $this->callInaccessibleMethod($component, 'buildExpectedAndExistingControllerClassName', 'Content');
		$this->assertSame($expectedClassName, $className);
	}

	/**
	 * @test
	 */
	public function getVendorNameAndExtensionKeyFromExtensionNameReturnsNullVendorNameForOldExtensionKey() {
		$component = $this->createInstance();
		$key = 'oldschool_key';
		list ($vendorName, $extensionKey) = $this->callInaccessibleMethod($component, 'getVendorNameAndExtensionKeyFromExtensionName', $key);
		$this->assertSame($key, $extensionKey);
		$this->assertNull($vendorName);
	}

	/**
	 * @test
	 */
	public function getVendorNameAndExtensionKeyFromExtensionNameReturnsExpectedValuePair() {
		$component = $this->createInstance();
		$vendor = 'Void';
		$name = 'Nameless';
		$key = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($name);
		list ($vendorName, $extensionKey) = $this->callInaccessibleMethod($component, 'getVendorNameAndExtensionKeyFromExtensionName', $vendor . '.' . $name);
		$this->assertSame($vendorName, $vendor);
		$this->assertSame($key, $extensionKey);
		$this->assertNotSame($name, $extensionKey);
	}

	/**
	 * @test
	 */
	public function canGenerateLabelFromLanguageFile() {
		$extensionName = 'Flux';
		$pluginName = 'Test';
		$controllerName = 'Content';
		$actionName = 'fake';
		$localLanguageFileRelativePath = '/Resources/Private/Language/locallang.xml';
		$labelPath = strtolower($pluginName . '.' . $controllerName . '.' . $actionName);
		$expectedLabel = 'LLL:EXT:' . \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName) . $localLanguageFileRelativePath . ':' . $labelPath;
		$label = $this->buildLabelForControllerAndAction($controllerName, $actionName, $localLanguageFileRelativePath);
		$this->assertSame($expectedLabel, $label);
	}

	/**
	 * @test
	 */
	public function canGenerateLabelFromActionMethodAnnotation() {
		$controllerName = 'Content';
		$actionName = 'fake';
		$expectedLabel = 'Fake Action';
		$label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
		$this->assertSame($expectedLabel, $label);
	}

	/**
	 * @test
	 */
	public function canGenerateDefaultLabelFromActionMethodWithoutHumanReadableAnnotation() {
		$controllerName = 'Content';
		$actionName = 'fakeWithoutDescription';
		$expectedLabel = $actionName . '->' . $controllerName;
		$label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
		$this->assertSame($expectedLabel, $label);
	}

	/**
	 * @test
	 */
	public function generatesDefaultLabelForControllerActionsWhichDoNotExist() {
		$controllerName = 'Content';
		$actionName = 'fictionalaction';
		$expectedLabel = $actionName . '->' . $controllerName;
		$label = $this->buildLabelForControllerAndAction($controllerName, $actionName);
		$this->assertSame($expectedLabel, $label);
	}

	/**
	 * @test
	 */
	public function prefixesLabelForActionsWithRequiredArgumentsWhenLanguageLabelsDisabled() {
		$extensionName = 'Flux';
		$pluginName = 'Test';
		$controllerName = 'Content';
		$actionName = 'fakeWithRequiredArgument';
		$component = $this->createInstance();
		$component->setExtensionName($extensionName);
		$component->setPluginName($pluginName);
		$component->setControllerName($controllerName);
		$component->setDisableLocalLanguageLabels(TRUE);
		$label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);
		$prefixedLabel = $this->callInaccessibleMethod($component, 'prefixLabel', $controllerName, $actionName, $label);
		$this->assertStringStartsWith('*', $prefixedLabel);
		$this->assertNotSame($label, $prefixedLabel);
	}

	/**
	 * @test
	 */
	public function respectsExcludedActions() {
		$actions = array(
			'Content' => 'render,fake'
		);
		$excludedActions = array(
			'Content' => 'fake',
		);
		/** @var Tx_Flux_Form_Field_ControllerActions $component */
		$component = $this->createInstance();
		$component->setExcludeActions($excludedActions);
		$component->setActions($actions);
		$component->setExtensionName('Flux');
		$items = $this->buildActions($component, FALSE);
		foreach ($items as $item) {
			$this->assertArrayNotHasKey('Content->fake', $item);
		}
	}

	/**
	 * @test
	 */
	public function skipsOtherControllersInActionsIfControllerSpecifiedInBothPropertyAndActions() {
		$actions = array(
			'Content' => 'fake',
			'Other' => 'fake'
		);
		class_alias('Tx_Flux_Controller_ContentController', 'Tx_Flux_Controller_OtherController');
		/** @var Tx_Flux_Form_Field_ControllerActions $component */
		$component = $this->createInstance();
		$component->setActions($actions);
		$component->setControllerName('Content');
		$component->setExtensionName('Flux');
		$items = $this->buildActions($component, FALSE);
		foreach ($items as $item) {
			$this->assertArrayNotHasKey('Other->fake', $item);
		}
	}

	/**
	 * @test
	 */
	public function skipsActionsWhichDoNotHaveAssociatedControllerMethods() {
		$actions = array(
			'Content' => 'fake,doesNotExist'
		);
		/** @var Tx_Flux_Form_Field_ControllerActions $component */
		$component = $this->createInstance();
		$component->setActions($actions);
		$component->setControllerName('Content');
		$component->setExtensionName('Flux');
		$items = $this->buildActions($component, FALSE);
		foreach ($items as $item) {
			$this->assertArrayNotHasKey('Other->doesNotExist', $item);
		}
	}

	/**
	 * @test
	 */
	public function supportsSubActions() {
		$actions = array(
			'Content' => 'fake'
		);
		$subActions = array(
			'Content' => array(
				'fake' => 'render'
			)
		);
		$expected = array(
			array('LLL:EXT:flux/Resources/Private/Language/locallang.xml:.content.fake', 'Content->fake;Content->render')
		);
		/** @var Tx_Flux_Form_Field_ControllerActions $component */
		$component = $this->createInstance();
		$component->setActions($actions);
		$component->setSubActions($subActions);
		$component->setExtensionName('Flux');
		$component->setControllerName('Content');
		$items = $this->buildActions($component, FALSE);
		$this->assertSame($expected, $items);
	}

	/**
	 * @param Tx_Flux_Form_Field_ControllerActions $component
	 * @param boolean $useDefaults
	 * @return array
	 */
	protected function buildActions(Tx_Flux_Form_Field_ControllerActions $component, $useDefaults = TRUE) {
		$actions = $component->getActions();
		if (TRUE === $useDefaults) {
			$component->setExtensionName('Flux');
			$component->setPluginName('Test');
			$component->setControllerName('Content');
			$component->setLocalLanguageFileRelativePath('/Resources/Private/Language/locallang.xml');
		}
		$items = $this->callInaccessibleMethod($component, 'buildItemsForActions', $actions);
		return $items;

	}

	/**
	 * @param string $controllerName
	 * @param string $actionName
	 * @param string $languageFileRelativeLocation
	 * @return string
	 */
	protected function buildLabelForControllerAndAction($controllerName, $actionName, $languageFileRelativeLocation = NULL) {
		$component = $this->createInstance();
		$component->setControllerName($controllerName);
		$component->setExtensionName('Flux');
		$component->setPluginName('Test');
		if (NULL !== $languageFileRelativeLocation) {
			$component->setLocalLanguageFileRelativePath($languageFileRelativeLocation);
		} else {
			$component->setDisableLocalLanguageLabels(TRUE);
		}
		$label = $this->callInaccessibleMethod($component, 'getLabelForControllerAction', $controllerName, $actionName);
		return $label;
	}

}
