<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * @package Flux
 * @subpackage Form
 */
abstract class Tx_Flux_Form_AbstractFormComponent {

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label = 'Unnamed FormComponent';

	/**
	 * @var Tx_Flux_Form_FormContainerInterface
	 */
	protected $parent;

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param array $settings
	 * @return Tx_Flux_Form_FormInterface
	 */
	public static function createFromDefinition(array $settings) {
		/** @var Tx_Extbase_Object_ObjectManagerInterface $objectManager */
		$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$className = get_called_class();
		/** @var Tx_Flux_FormInterface $object */
		$object = $objectManager->get($className);
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = Tx_Extbase_Reflection_ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($object, $setterMethodName)) {
				Tx_Extbase_Reflection_ObjectAccess::setProperty($object, $settingName, $settingValue);
			}
		}
		if (TRUE === $object instanceof Tx_Flux_Form_FieldContainerInterface && TRUE === isset($settings['fields'])) {
			foreach ($settings['fields'] as $fieldName => $fieldSettings) {
				if (FALSE === isset($fieldSettings['name'])) {
					$fieldSettings['name'] = $fieldName;
				}
				$field = Tx_Flux_Form_AbstractFormField::createFromDefinition($fieldSettings);
				$object->add($field);
			}
		}
		return $object;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function createField($type, $name, $label = NULL) {
		/** @var Tx_Flux_Form_FieldInterface $component */
		$component = $this->objectManager->get('Tx_Flux_Form_Field_' . $type);
		$component->setName($name);
		$component->setLabel($label);
		return $component;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_ContainerInterface
	 */
	public function createContainer($type, $name, $label = NULL) {
		/** @var Tx_Flux_Form_ContainerInterface $component */
		$component = $this->objectManager->get('Tx_Flux_Form_Container_' . $type);
		$component->setName($name);
		$component->setLabel($label);
		return $component;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL) {
		/** @var Tx_Flux_Form_WizardInterface $component */
		$component = $this->objectManager->get('Tx_Flux_Form_Wizard_' . $type);
		$component->setName($name);
		$component->setLabel($label);
		return $component;
	}

	/**
	 * @param string $name
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $label
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		$label = $this->label;
		$name = $this->getName();
		$root = $this->getRoot();
		if (FALSE === $root instanceof Tx_Flux_Form) {
			return $label;
		}
		$id = $root->getName();
		$extensionName = $root->getExtensionName();
		$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($extensionName);
		if (TRUE === isset($label) && FALSE === empty($label)) {
			return $label;
		} elseif ((TRUE === empty($extensionKey) || FALSE === t3lib_extMgm::isLoaded($extensionKey)) && TRUE === empty($label)) {
			return $name;
		}
		$prefix = '';
		if (TRUE === $this instanceof Tx_Flux_Form_Container_Sheet) {
			$prefix = 'sheets';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Section) {
			$prefix = 'sections';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Content) {
			$prefix = 'areas';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Object) {
			$prefix = 'objects';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_FieldInterface) {
			if (TRUE === $this->isChildOfType('Object')) {
				$prefix = 'objects.' . $this->getParent()->getName();
			} else {
				$prefix = 'fields';
			}
		}
		$filePrefix = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xml';
		$labelIdentifier = 'flux.' . $id . (TRUE === empty($prefix) ? '' : '.' . $prefix . '.' . $name);
		$this->configurationService->updateLanguageSourceFileIfUpdateFeatureIsEnabledAndIdentifierIsMissing($filePrefix, $labelIdentifier, $id);
		return $filePrefix . ':' . $labelIdentifier;
	}

	/**
	 * @param Tx_Flux_Form_ContainerInterface $parent
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @return Tx_Flux_Form_FormContainerInterface
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @return Tx_Flux_Form_FormContainerInterface
	 */
	public function getRoot() {
		if (NULL === $this->getParent()) {
			return $this;
		}
		return $this->getParent()->getRoot();
	}

	/**
	 * @param string $type
	 * @return boolean
	 */
	public function isChildOfType($type) {
		return ('Tx_Flux_Form_Container_' . $type === get_class($this->getParent()));
	}

}
