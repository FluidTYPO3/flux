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

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Container;
use FluidTYPO3\Flux\Form\Container\Content;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Object;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @package Flux
 * @subpackage Form
 */
abstract class AbstractFormComponent implements FormInterface {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var ConfigurationManagerInterface
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
	protected $localLanguageFileRelativePath = '/Resources/Private/Language/locallang.xlf';

	/**
	 * @var string
	 */
	protected $extensionName = 'FluidTYPO3.Flux';

	/**
	 * @var ContainerInterface
	 */
	protected $parent;

	/**
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param array $settings
	 * @return FormInterface
	 */
	public static function create(array $settings = array()) {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$className = get_called_class();
		/** @var FormInterface $object */
		$object = $objectManager->get($className);
		foreach ($settings as $settingName => $settingValue) {
			$setterMethodName = ObjectAccess::buildSetterMethodName($settingName);
			if (TRUE === method_exists($object, $setterMethodName)) {
				ObjectAccess::setProperty($object, $settingName, $settingValue);
			}
		}
		if (TRUE === $object instanceof FieldContainerInterface && TRUE === isset($settings['fields'])) {
			foreach ($settings['fields'] as $fieldName => $fieldSettings) {
				if (FALSE === isset($fieldSettings['name'])) {
					$fieldSettings['name'] = $fieldName;
				}
				$field = AbstractFormField::create($fieldSettings);
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
		$className = str_replace('/', '\\', $type);
		$className = TRUE === class_exists($prefix . '\\' . $className) ? $prefix . '\\' . $className : $className;
		return $className;
	}

	/**
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @return FieldInterface
	 */
	public function createField($type, $name, $label = NULL) {
		/** @var FieldInterface $component */
		$className = $this->createComponentClassName($type, 'FluidTYPO3\Flux\Form\Field');
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
	 * @return ContainerInterface
	 */
	public function createContainer($type, $name, $label = NULL) {
		/** @var ContainerInterface $component */
		$className = $this->createComponentClassName($type, 'FluidTYPO3\Flux\Form\Container');
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
	 * @return WizardInterface
	 */
	public function createWizard($type, $name, $label = NULL) {
		/** @var WizardInterface $component */
		$className = $this->createComponentClassName($type, 'FluidTYPO3\Flux\Form\Wizard');
		$component = $this->objectManager->get($className);
		$component->setName($name);
		$component->setLabel($label);
		$component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
		$component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
		return $component;
	}

	/**
	 * @param string $name
	 * @return FormInterface
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
	 * @param string $extensionName
	 * @return FormInterface
	 */
	public function setExtensionName($extensionName) {
		$this->extensionName = $extensionName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getExtensionName() {
		return $this->extensionName;
	}

	/**
	 * @param string $label
	 * @return FormInterface
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		$prefix = '';
		if (TRUE === $this instanceof Sheet) {
			$prefix = 'sheets';
		} elseif (TRUE === $this instanceof Section) {
			$prefix = 'sections';
		} elseif (TRUE === $this instanceof Grid) {
			$prefix = 'grids';
		} elseif (TRUE === $this instanceof Column) {
			$prefix = 'columns';
		} elseif (TRUE === $this instanceof Object) {
			$prefix = 'objects';
		} elseif (TRUE === $this instanceof Content) {
			$prefix = 'areas';
		} elseif (TRUE === $this instanceof Container) {
			$prefix = 'containers';
		} elseif (TRUE === $this instanceof FieldInterface) {
			if (TRUE === $this->isChildOfType('Object')) {
				$prefix = 'objects.' . $this->getParent()->getName();
			} else {
				$prefix = 'fields';
			}
		}
		return trim($prefix . '.' . $this->getName(), '.');
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
		if (FALSE === $root instanceof Form) {
			$id = 'form';
			$extensionName = $this->extensionName;
		} else {
			$id = $root->getName();
			$extensionName = $root->getExtensionName();
		}
		$extensionKey = FALSE === strpos($extensionName, '.') ? $extensionName : substr($extensionName, strpos($extensionName, '.') + 1);
		$extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($extensionKey);
		if (FALSE === empty($label)) {
			if (0 === strpos($label, 'LLL:') && 0 !== strpos($label, 'LLL:EXT:')) {
				return LocalizationUtility::translate(substr($label, 4), $extensionKey);
			} else {
				return $label;
			}
		}
		if ((TRUE === empty($extensionKey) || FALSE === ExtensionManagementUtility::isLoaded($extensionKey))) {
			return $name;
		}
		if (FALSE === $this instanceof Form) {
			$path = $this->getPath();
		} else {
			$path = '';
		}
		$relativeFilePath = $this->getLocalLanguageFileRelativePath();
		$relativeFilePath = ltrim($relativeFilePath, '/');
		$filePrefix = 'LLL:EXT:' . $extensionKey . '/' . $relativeFilePath;
		$labelIdentifier = $filePrefix . ':' . trim('flux.' . $id . '.' . $path, '.');
		$translated = LocalizationUtility::translate($labelIdentifier, $extensionKey);
		return (NULL !== $translated ? $translated : $labelIdentifier);
	}

	/**
	 * @param string $localLanguageFileRelativePath
	 * @return FormInterface
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
	 * @return FormInterface
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
	 * @param ContainerInterface $parent
	 * @return FormInterface
	 */
	public function setParent($parent) {
		$this->parent = $parent;
		return $this;
	}

	/**
	 * @return ContainerInterface
	 */
	public function getParent() {
		return $this->parent;
	}

	/**
	 * @param array $variables
	 * @return FormInterface
	 */
	public function setVariables($variables) {
		$this->variables = (array) $variables;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return FormInterface
	 */
	public function setVariable($name, $value) {
		$this->variables[$name] = $value;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getVariable($name) {
		return TRUE === isset($this->variables[$name]) ? $this->variables[$name] : NULL;
	}

	/**
	 * @return ContainerInterface
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
		$parent = $this->getParent();
		if ($parent === NULL) {
			return FALSE;
		}
		return ('FluidTYPO3\Flux\Form\Container\\' . $type === get_class($parent) || TRUE === is_a($parent, $type));
	}

}
