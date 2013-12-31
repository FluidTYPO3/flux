<?php
namespace FluidTYPO3\Flux\Form;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
use FluidTYPO3\Flux\Form\Container\Section;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;

/**
 * @package Flux
 * @subpackage Form
 */
abstract class AbstractFormField extends AbstractFormComponent implements FieldInterface {

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
		$this->wizards = new \SplObjectStorage();
	}

	/**
	 * @param array $settings
	 * @return FieldInterface
	 * @throws \RuntimeException
	 */
	public static function create(array $settings = array()) {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		if ('Section' === $settings['type']) {
			return Section::create($settings);
		} else {
			$prefix = 'FluidTYPO3\Flux\Form\Field\\';
			$type = $settings['type'];
			$className = str_replace('/', '\\', $type);
			$className = TRUE === class_exists($prefix . $className) ? $prefix . $className : $className;
		}
		if (FALSE === class_exists($className)) {
			$className = $settings['type'];
		}
		if (FALSE === class_exists($className)) {
			throw new \RuntimeException('Invalid class- or type-name used in type of field "' . $settings['name'] . '"; "' . $className . '" is invalid', 1375373527);
		}
		/** @var FormInterface $object */
		$object = $objectManager->get($className);
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = 'set' . ucfirst($settingName);
			if (TRUE === method_exists($object, $setterMethodName)) {
				call_user_func_array(array($object, $setterMethodName), array($settingValue));
			}
		}
		return $object;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL) {
		$wizard = parent::createWizard($type, $name, $label);
		$this->add($wizard);
		return $wizard;
	}

	/**
	 * @param WizardInterface $wizard
	 * @return FieldInterface
	 */
	public function add(WizardInterface $wizard) {
		if (FALSE === $this->wizards->contains($wizard)) {
			$this->wizards->attach($wizard);
			$wizard->setParent($this);
		}
		return $this;
	}

	/**
	 * @param string $wizardName
	 * @return WizardInterface|FALSE
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
	 * @return WizardInterface|FALSE
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
				'userFunc' => 'FluidTYPO3\Flux\UserFunction\ClearValueWizard->renderField',
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
		/** @var FormInterface[] $children */
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
	 * @return FieldInterface
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
