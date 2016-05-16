<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * AbstractFormField
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
	 * @var boolean
	 */
	protected $inherit = TRUE;

	/**
	 * @var boolean
	 */
	protected $inheritEmpty = FALSE;

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
	 * @var string
	 */
	protected $validate;

	/**
	 * @var \SplObjectStorage
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
	 * @param mixed $childOrChildName
	 * @return boolean
	 */
	public function has($childOrChildName) {
		$name = (TRUE === $childOrChildName instanceof FormInterface) ? $childOrChildName->getName() : $childOrChildName;
		return (FALSE !== $this->get($name));
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
			'label' => $this->getLabel(),
			'exclude' => intval($this->getExclude()),
			'config' => $configuration,
			'displayCond' => $this->getDisplayCondition()
		);
		if (TRUE === isset($configuration['defaultExtras'])) {
			$fieldStructureArray['defaultExtras'] = $configuration['defaultExtras'];
			unset($fieldStructureArray['config']['defaultExtras']);
		}
		$wizards = $this->buildChildren($this->wizards);
		if (TRUE === $this->getClearable()) {
			array_push($wizards, array(
				'type' => 'userFunc',
				'userFunc' => 'FluidTYPO3\Flux\UserFunction\ClearValueWizard->renderField',
				'params' => array(
					'itemName' => $this->getName(),
				),
			));
		}
		$fieldStructureArray['config']['wizards'] = $wizards;
		if (TRUE === $this->getRequestUpdate()) {
			$fieldStructureArray['onChange'] = 'reload';
		}
		return $fieldStructureArray;
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
		return $this->default;
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
	 * @param string $validate
	 * @return FieldInterface
	 */
	public function setValidate($validate) {
		$this->validate = $validate;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getValidate() {
		if (FALSE === (boolean) $this->getRequired()) {
			$validate = $this->validate;
		} else {
			if (TRUE === empty($this->validate)) {
				$validate = 'required';
			} else {
				$validators = GeneralUtility::trimExplode(',', $this->validate);
				array_push($validators, 'required');
				$validate = implode(',', $validators);
			}
		}
		return $validate;
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

	/**
	 * @param array $structure
	 * @return ContainerInterface
	 */
	public function modify(array $structure) {
		if (TRUE === isset($structure['wizards'])) {
			foreach ((array) $structure['wizards'] as $index => $wizardData) {
				$wizardName = TRUE === isset($wizardData['name']) ? $wizardData['name'] : $index;
				// check if field already exists - if it does, modify it. If it does not, create it.
				if (TRUE === $this->has($wizardName)) {
					$field = $this->get($wizardName);
				} else {
					$wizardType = TRUE === isset($wizardData['type']) ? $wizardData['type'] : 'None';
					$field = $this->createWizard($wizardType, $wizardName);
				}
				$field->modify($wizardData);
			}
		}
		return parent::modify($structure);
	}

}
