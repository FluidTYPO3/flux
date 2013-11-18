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
abstract class Tx_Flux_Form_AbstractFormField extends Tx_Flux_Form_AbstractFormComponent implements Tx_Flux_Form_FieldInterface {

	/**
	 * @var boolean
	 */
	protected $required = FALSE;

	/**
	 * @var mixed
	 */
	protected $default;

	/**
	 * @var string
	 */
	protected $transform;

	/**
	 * @var string
	 */
	protected $displayCondition = NULL;

	/**
	 * @var boolean
	 */
	protected $requestUpdate = FALSE;

	/**
	 * @var integer
	 */
	protected $inherit = 0;

	/**
	 * @var boolean
	 */
	protected $inheritEmpty = FALSE;

	/**
	 * @var boolean
	 */
	protected $stopInheritance = FALSE;

	/**
	 * @var boolean
	 */
	protected $clearable = FALSE;

	/**
	 * @var boolean
	 */
	protected $exclude = TRUE;

	/**
	 * @var boolean
	 */
	protected $enable = TRUE;

	/**
	 * @var SplObjectStorage
	 */
	protected $wizards;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->wizards = new SplObjectStorage();
	}

	/**
	 * @param array $settings
	 * @return Tx_Flux_Form_FormFieldInterface
	 * @throws Exception
	 */
	public static function create(array $settings = array()) {
		/** @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		if ('Section' === $settings['type']) {
			return Tx_Flux_Form_Container_Section::create($settings);
		} else {
			$prefix = 'Tx_Flux_Form_Field_';
			$type = $settings['type'];
			if (FALSE === strpos($type, '/')) {
				$className = $type;
			} else {
				$className = str_replace('/', '\\', $type);
				// Until Namespaces replace to _
				$className = str_replace('\\', '_', $className);
			}
			$className = TRUE === class_exists($prefix . $className) ? $prefix . $className : $className;
		}
		if (FALSE === class_exists($className)) {
			$className = $settings['type'];
		}
		if (FALSE === class_exists($className)) {
			throw new RuntimeException('Invalid class- or type-name used in type of field "' . $settings['name'] . '"; "' . $className . '" is invalid', 1375373527);
		}
		/** @var Tx_Flux_FormInterface $object */
		$object = $objectManager->get($className);
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = \TYPO3\CMS\Extbase\Reflection\ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($object, $setterMethodName)) {
				\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($object, $settingName, $settingValue);
			}
		}
		return $object;
	}

	/**
	 * @param Tx_Flux_Form_WizardInterface $wizard
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function add(Tx_Flux_Form_WizardInterface $wizard) {
		if (FALSE === $this->wizards->contains($wizard)) {
			$this->wizards->attach($wizard);
			$wizard->setParent($this);
		}
		return $this;
	}

	/**
	 * @param string $wizardName
	 * @return Tx_Flux_Form_WizardInterface|FALSE
	 */
	public function get($wizardName) {
		foreach ($this->wizards as $wizard) {
			if ($wizardName === $wizard->getName()) {
				return $wizard;
			}
		}
		return FALSE;
	}

	/**
	 * @param string $wizardName
	 * @return Tx_Flux_Form_WizardInterface|FALSE
	 */
	public function remove($wizardName) {
		foreach ($this->wizards as $wizard) {
			if ($wizardName === $wizard->getName()) {
				$this->wizards->detach($wizard);
				$this->wizards->rewind();
				$wizard->setParent(NULL);
				return $wizard;
			}
		}
		return FALSE;
	}

	/**
	 * Creates a TCEforms configuration array based on the
	 * configuration stored in this ViewHelper. Calls the
	 * expected-to-be-overridden stub method getConfiguration()
	 * to return the TCE field configuration - see that method
	 * for information about how to implement that method.
	 *
	 * @return array
	 */
	public function build() {
		if (FALSE === $this->getEnable()) {
			return array();
		}
		$configuration = $this->buildConfiguration();
		$fieldStructureArray = array(
			'TCEforms' => array(
				'label' => $this->getLabel(),
				'exclude' => intval($this->getExclude()),
				'config' => $configuration,
				'displayCond' => $this->getDisplayCondition(),
			)
		);
		if (TRUE === isset($configuration['defaultExtras'])) {
			$fieldStructureArray['TCEforms']['defaultExtras'] = $configuration['defaultExtras'];
			unset($fieldStructureArray['TCEforms']['config']['defaultExtras']);
		}
		$wizards = $this->buildChildren();
		if (TRUE === $this->getClearable()) {
			array_push($wizards, array(
				'type' => 'userFunc',
				'userFunc' => 'Tx_Flux_UserFunction_ClearValueWizard->renderField',
				'params' => array(
					'itemName' => $this->getName(),
				),
			));
		}
		$fieldStructureArray['TCEforms']['config']['wizards'] = $wizards;
		if (TRUE === $this->getRequestUpdate()) {
			$fieldStructureArray['TCEforms']['onChange'] = 'reload';
		}
		return $fieldStructureArray;
	}

	/**
	 * @return array
	 */
	protected function buildChildren() {
		$structure = array();
		/** @var Tx_Flux_Form_FormInterface[] $children */
		$children = $this->wizards;
		foreach ($children as $child) {
			$name = $child->getName();
			$structure[$name] = $child->build();
		}
		return $structure;
	}

	/**
	 * @param string $type
	 * @return array
	 */
	protected function prepareConfiguration($type) {
		$fieldConfiguration = array(
			'type' => $type,
			'transform' => $this->getTransform(),
			'default' => $this->getDefault(),
		);
		return $fieldConfiguration;
	}

	/**
	 * @param boolean $required
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequired($required) {
		$this->required = (boolean) $required;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequired() {
		return (boolean) $this->required;
	}

	/**
	 * @param mixed $default
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDefault() {
		if (FALSE === empty($this->default)) {
			$defaultValue = $this->default;
		} else {
			$defaultValue = NULL;
		}
		return $defaultValue;
	}

	/**
	 * @param string $transform
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setTransform($transform) {
		$this->transform = $transform;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTransform() {
		return $this->transform;
	}

	/**
	 * @param string $displayCondition
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setDisplayCondition($displayCondition) {
		$this->displayCondition = $displayCondition;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDisplayCondition() {
		return $this->displayCondition;
	}

	/**
	 * @param boolean $requestUpdate
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setRequestUpdate($requestUpdate) {
		$this->requestUpdate = (boolean) $requestUpdate;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRequestUpdate() {
		return (boolean) $this->requestUpdate;
	}

	/**
	 * @param integer $inherit
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInherit($inherit) {
		$this->inherit = $inherit;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getInherit() {
		return $this->inherit;
	}

	/**
	 * @param boolean $inheritEmpty
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setInheritEmpty($inheritEmpty) {
		$this->inheritEmpty = (boolean) $inheritEmpty;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getInheritEmpty() {
		return (boolean) $this->inheritEmpty;
	}

	/**
	 * @param boolean $stopInheritance
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setStopInheritance($stopInheritance) {
		$this->stopInheritance = (boolean) $stopInheritance;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getStopInheritance() {
		return (boolean) $this->stopInheritance;
	}

	/**
	 * @param boolean $exclude
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setExclude($exclude) {
		$this->exclude = (boolean) $exclude;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getExclude() {
		return (boolean) $this->exclude;
	}

	/**
	 * @param boolean $enable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setEnable($enable) {
		$this->enable = (boolean) $enable;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnable() {
		return (boolean) $this->enable;
	}

	/**
	 * @param boolean $clearable
	 * @return Tx_Flux_Form_FieldInterface
	 */
	public function setClearable($clearable) {
		$this->clearable = (boolean) $clearable;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getClearable() {
		return (boolean) $this->clearable;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		return 0 < $this->wizards->count();
	}

}
