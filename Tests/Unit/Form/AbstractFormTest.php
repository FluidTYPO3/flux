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
abstract class Tx_Flux_Tests_Functional_Form_AbstractFormTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array('name' => 'test', 'label' => 'Test field');

	/**
	 * @return Tx_Flux_Form_FormInterface
	 */
	protected function createInstance() {
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		return $instance;
	}

	/**
	 * @test
	 */
	public function canGenerateRawLabelWhenLanguageLabelsDisabled() {
		$instance = $this->createInstance();
		$instance->setLabel(NULL);
		$instance->setDisableLocalLanguageLabels(TRUE);
		$this->assertNull($instance->getLabel());
	}

	/**
	 * @test
	 */
	public function canGenerateLocalisableLabel() {
		$instance = $this->createInstance();
		$instance->setLabel(NULL);
		if (FALSE === $instance instanceof Tx_Flux_Form) {
			/** @var Tx_Flux_Form $form */
			$form = $this->objectManager->get('Tx_Flux_Form');
			$form->setId('testFormId');
			$form->setExtensionName('Flux');
			$form->add($instance);
			$label = $instance->getLabel();
			$this->assertStringStartsWith('LLL:EXT:flux/Resources/Private/Language/locallang.xml:flux', $label);
			$this->assertContains('testFormId', $label);
		}
	}

	/**
	 * @return string
	 */
	protected function getObjectClassName() {
		$class = get_class($this);
		$segments = explode('_', $class);
		$objectName = substr(array_pop($segments), 0, -4);
		$scope = array_pop($segments);
		return 'Tx_Flux_Form_' . $scope . '_' . $objectName;
	}

	/**
	 * @test
	 * @param array $chainPropertiesAndValues
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function canChainAllChainableSetters($chainPropertiesAndValues = NULL) {
		if (NULL === $chainPropertiesAndValues) {
			$chainPropertiesAndValues = $this->chainProperties;
		}
		$instance = $this->createInstance();
		foreach ($chainPropertiesAndValues as $propertyName => $propertValue) {
			$setterMethodName = Tx_Extbase_Reflection_ObjectAccess::buildSetterMethodName($propertyName);
			$chained = call_user_func_array(array($instance, $setterMethodName), array($propertValue));
			$this->assertSame($instance, $chained, 'The setter ' . $setterMethodName . ' on ' . $this->getObjectClassName() . ' does not support chaining.');
			if ($chained === $instance) {
				$instance = $chained;
			}
		}
		$this->performTestBuild($instance);
		return $instance;
	}

	/**
	 * @test
	 */
	public function ifObjectIsFieldContainerItSupportsFetchingFields() {
		$instance = $this->createInstance();
		if (TRUE === $instance instanceof Tx_Flux_Form_FieldContainerInterface) {
			$field = $instance->createField('Input', 'test');
			$instance->add($field);
			$fields = $instance->getFields();
			$this->assertNotEmpty($fields, 'The class ' . $this->getObjectClassName() . ' does not appear to support the required FieldContainerInterface implementation');
			$this->performTestBuild($instance);
		}
	}

	/**
	 * @test
	 */
	public function returnsNameInsteadOfEmptyLabelWhenFormsExtensionKeyAndLabelAreBothEmpty() {
		$instance = $this->createInstance();
		if (FALSE === $instance instanceof Tx_Flux_Form && TRUE === $instance instanceof Tx_Flux_Form_FieldInterface) {
			/** @var Tx_Flux_Form $form */
			$form = $this->objectManager->get('Tx_Flux_Form');
			$form->setExtensionName(NULL);
			$form->add($instance);
		}
		$instance->setName('test');
		$instance->setLabel(NULL);
		$this->performTestBuild($instance);

	}

	/**
	 * @test
	 */
	public function canCallAllGetterCounterpartsForChainableSetters() {
		$instance = $this->createInstance();
		foreach ($this->chainProperties as $propertyName => $propertValue) {
			Tx_Extbase_Reflection_ObjectAccess::getProperty($instance, $propertyName);
		}
		$this->performTestBuild($instance);
	}

	/**
	 * @param Tx_Flux_Form_FieldInterface
	 * @return array
	 */
	protected function performTestBuild($instance) {
		$configuration = $instance->build();
		$this->assertIsArray($configuration);
		return $configuration;
	}

	/**
	 * @test
	 */
	public function canBuildConfiguration() {
		$instance = $this->canChainAllChainableSetters();
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinition() {
		$properties = array($this->chainProperties);
		if (TRUE === $this instanceof Tx_Flux_Tests_Functional_Form_Field_AbstractFieldTest) {
			$properties['type'] = substr(array_pop(explode('_', get_class($this))), 0, -4);
		}
		$instance = call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
		$this->assertInstanceOf('Tx_Flux_Form_FormInterface', $instance);
	}

}
