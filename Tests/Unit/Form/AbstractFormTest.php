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
	public function canGetLabel() {
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		$instance->setName('test');
		if (TRUE === $instance instanceof Tx_Flux_Form_FieldInterface || TRUE === $instance instanceof Tx_Flux_Form_ContainerInterface) {
			$form = Tx_Flux_Form::create(array('extensionKey' => 'flux'));
			$form->add($instance);
		}
		$label = $instance->getLabel();
		$this->assertNotEmpty($label);
	}

	/**
	 * @test
	 */
	public function canAutoWriteLabel() {
		$languageFile = 'LLL:typo3temp/test.xml';
		$absoluteLanguageFile = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(substr($languageFile, 4));
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		$instance->setName('thisIsASpecialFieldName');
		$id = 'somename';
		$form = Tx_Flux_Form::create();
		$form->setId($id);
		$form->setExtensionName('Flux');
		if (FALSE === $instance instanceof Tx_Flux_Form_WizardInterface) {
			$form->add($instance);
		} else {
			$field = $form->createField('Input', 'dummy');
			$field->add($instance);
		}
		$probe = $instance->getName();
		$label = $instance->getLabel();
		$backup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles'];
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles'] = 1;
		$this->objectManager->get('Tx_Flux_Service_LanguageFileService')->reset();
		// note: double call is not an error - designed to trigger caches and assumes no errors happens during that phase
		$this->callInaccessibleMethod($instance, 'writeLanguageLabel', $languageFile, array_pop(explode(':', $label)), $id);
		$this->objectManager->get('Tx_Flux_Service_LanguageFileService')->reset();
		$this->callInaccessibleMethod($instance, 'writeLanguageLabel', $languageFile, array_pop(explode(':', $label)), $id);
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles'] = $backup;
		$this->assertNotEmpty($label);
		$this->assertFileExists($absoluteLanguageFile);
		$this->assertContains($probe, file_get_contents($absoluteLanguageFile));
		unlink($absoluteLanguageFile);
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
		$class = substr($class, 0, -4);
		return $class;
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
			$setterMethodName = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::buildSetterMethodName($propertyName);
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
			\TYPO3\CMS\Extbase\Reflection\ObjectAccess::getProperty($instance, $propertyName);
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
		$class = $this->getObjectClassName();
		$type = implode('/', array_slice(explode('_', substr($class, 13)), 1));
		$properties['type'] = $type;
		$instance = call_user_func_array(array($class, 'create'), array($properties));
		$this->assertInstanceOf('Tx_Flux_Form_FormInterface', $instance);
	}

	/**
	 * @test
	 */
	public function canUseShorthandLanguageLabel() {
		$className = $this->getObjectClassName();
		$instance = $this->getMock($className, array('getExtensionKey', 'getName', 'getRoot'));
		$instance->expects($this->never())->method('getExtensioKey');
		$instance->expects($this->once())->method('getRoot')->will($this->returnValue(NULL));
		$instance->expects($this->once())->method('getName')->will($this->returnValue('form'));
		$instance->setLabel('LLL:tt_content.tx_flux_container');
		$result = $instance->getLabel();
		$this->assertSame(Tx_Extbase_Utility_Localization::translate('tt_content.tx_flux_container', 'Flux'), $result);
	}

}
