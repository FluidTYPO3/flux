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
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label = NULL;

	/**
	 * If TRUE, disables LLL label usage and always returns the
	 * raw value of $label.
	 *
	 * @var boolean
	 */
	protected $disableLocalLanguageLabels = FALSE;

	/**
	 * Relative (from extension $extensionName) path to locallang
	 * file containing labels for the LLL values built by this class.
	 *
	 * @var string
	 */
	protected $localLanguageFileRelativePath = '/Resources/Private/Language/locallang.xml';

	/**
	 * @var Tx_Flux_Form_FormContainerInterface
	 */
	protected $parent;

	/**
	 * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager) {
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
	 * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param array $settings
	 * @return Tx_Flux_Form_FormInterface
	 */
	public static function create(array $settings = array()) {
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$className = get_called_class();
		/** @var Tx_Flux_FormInterface $object */
		$object = $objectManager->get($className);
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($object, $setterMethodName)) {
				\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($object, $settingName, $settingValue);
			}
		}
		if (TRUE === $object instanceof Tx_Flux_Form_FieldContainerInterface && TRUE === isset($settings['fields'])) {
			foreach ($settings['fields'] as $fieldName => $fieldSettings) {
				if (FALSE === isset($fieldSettings['name'])) {
					$fieldSettings['name'] = $fieldName;
				}
				$field = Tx_Flux_Form_AbstractFormField::create($fieldSettings);
				$object->add($field);
			}
		}
		return $object;
	}

	/**
	 * @param string $type
	 * @param string $prefix
	 * @return string
	 */
	protected function createComponentClassName($type, $prefix) {
		if (FALSE === strpos($type, '/')) {
			$className = $type;
		} else {
			$className = str_replace('/', '\\', $type);
			// Until Namespaces replace to _
			$className = str_replace('\\', '_', $className);
		}
		$className = TRUE === class_exists($prefix . $className) ? $prefix . $className : $className;
		return $className;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function createField($type, $name, $label = NULL) {
		/** @var Tx_Flux_Form_FieldInterface $component */
		$className = $this->createComponentClassName($type, 'Tx_Flux_Form_Field_');
		$component = $this->objectManager->get($className);
		$component->setName($name);
		$component->setLabel($label);
		$component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
		$component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
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
		$className = $this->createComponentClassName($type, 'Tx_Flux_Form_Container_');
		$component = $this->objectManager->get($className);
		$component->setName($name);
		$component->setLabel($label);
		$component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
		$component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
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
		$className = $this->createComponentClassName($type, 'Tx_Flux_Form_Wizard_');
		$component = $this->objectManager->get($className);
		$component->setName($name);
		$component->setLabel($label);
		$component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
		$component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
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
		if (TRUE === $this->getDisableLocalLanguageLabels()) {
			return $label;
		}
		$name = $this->getName();
		$root = $this->getRoot();
		if (FALSE === $root instanceof Tx_Flux_Form) {
			$id = 'form';
			$extensionName = 'Flux';
		} else {
			$id = $root->getName();
			$extensionName = $root->getExtensionName();
		}
		$extensionKey = \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored($extensionName);
		if (FALSE === empty($label)) {
			if (0 === strpos($label, 'LLL:') && 0 !== strpos($label, 'LLL:EXT:')) {
				return Tx_Extbase_Utility_Localization::translate(substr($label, 4), $extensionKey);
			} else {
				return $label;
			}
		}
		if ((TRUE === empty($extensionKey) || FALSE === \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($extensionKey))) {
			return $name;
		}
		$prefix = '';
		if (TRUE === $this instanceof Tx_Flux_Form_Container_Sheet) {
			$prefix = 'sheets';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Section) {
			$prefix = 'sections';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Grid) {
			$prefix = 'grids';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Column) {
			$prefix = 'columns';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Object) {
			$prefix = 'objects';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Content) {
			$prefix = 'areas';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_Container_Container) {
			$prefix = 'containers';
		} elseif (TRUE === $this instanceof Tx_Flux_Form_FieldInterface) {
			if (TRUE === $this->isChildOfType('Object')) {
				$prefix = 'objects.' . $this->getParent()->getName();
			} else {
				$prefix = 'fields';
			}
		}
		$filePrefix = 'LLL:EXT:' . $extensionKey . $this->localLanguageFileRelativePath;
		$labelIdentifier = 'flux.' . $id . (TRUE === empty($prefix) ? '' : '.' . $prefix . '.' . $name);
		$this->writeLanguageLabel($filePrefix, $labelIdentifier, $id);
		return $filePrefix . ':' . $labelIdentifier;
	}

	/**
	 * @param string $filePrefix
	 * @param string $labelIdentifier
	 * @param string $id
	 * @return void
	 */
	protected function writeLanguageLabel($filePrefix, $labelIdentifier, $id) {
		if (TRUE === (boolean) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles']) {
			$this->objectManager->get('Tx_Flux_Service_LanguageFileService')->writeLanguageLabel($filePrefix, $labelIdentifier, $id);
		}
	}

	/**
	 * @param string $localLanguageFileRelativePath
	 * @return Tx_Flux_FormInterface
	 */
	public function setLocalLanguageFileRelativePath($localLanguageFileRelativePath) {
		$this->localLanguageFileRelativePath = $localLanguageFileRelativePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocalLanguageFileRelativePath() {
		return $this->localLanguageFileRelativePath;
	}


	/**
	 * @param boolean $disableLocalLanguageLabels
	 * @return Tx_Flux_FormInterface
	 */
	public function setDisableLocalLanguageLabels($disableLocalLanguageLabels) {
		$this->disableLocalLanguageLabels = (boolean) $disableLocalLanguageLabels;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getDisableLocalLanguageLabels() {
		return (boolean) $this->disableLocalLanguageLabels;
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
