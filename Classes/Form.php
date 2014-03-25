<?php
namespace FluidTYPO3\Flux;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use FluidTYPO3\Flux\Outlet\OutletInterface;

/**
 * @package Flux
 */
class Form extends Form\AbstractFormContainer implements Form\FieldContainerInterface {

	const POSITION_TOP = 'top';
	const POSITION_BOTTOM = 'bottom';
	const POSITION_BOTH = 'both';
	const POSITION_NONE = 'none';
	const CONTROL_INFO = 'info';
	const CONTROL_NEW = 'new';
	const CONTROL_DRAGDROP = 'dragdrop';
	const CONTROL_SORT = 'sort';
	const CONTROL_HIDE = 'hide';
	const CONTROL_DELETE = 'delete';
	const CONTROL_LOCALISE = 'localize';

	/**
	 * if FALSE, disables this form.
	 *
	 * @var boolean
	 */
	protected $enabled = TRUE;

	/**
	 * Machine-readable, lowerCamelCase ID of this form. DOM compatible.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Optional icon which represents the form.
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * Logical, human-readable or LLL group name this Form belongs to.
	 *
	 * @var string
	 */
	protected $group;

	/**
	 * Should be set to contain the extension name in UpperCamelCase of
	 * the extension implementing this form object.
	 *
	 * @var string
	 */
	protected $extensionName;

	/**
	 * If TRUE, removes sheet wrappers if there is only a single sheet.
	 *
	 * @var boolean
	 */
	protected $compact = FALSE;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var OutletInterface
	 */
	protected $outlet;

	/**
	 * @param array $settings
	 * @return Form
	 */
	public static function create(array $settings = array()) {
		$form = parent::create($settings);
		if (TRUE === isset($settings['sheets'])) {
			foreach ($settings['sheets'] as $sheetName => $sheetSettings) {
				if (FALSE === isset($sheetSettings['name'])) {
					$sheetSettings['name'] = $sheetName;
				}
				$sheet = Form\Container\Sheet::create($sheetSettings);
				$form->add($sheet);
			}
		}
		return $form;
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		/** @var Form\Container\Sheet $defaultSheet */
		$defaultSheet = $this->objectManager->get('FluidTYPO3\Flux\Form\Container\Sheet');
		$defaultSheet->setName('options');
		$defaultSheet->setLabel(LocalizationUtility::translate('tt_content.tx_flux_options', 'Flux'));
		$this->add($defaultSheet);
		$this->outlet = $this->objectManager->get('FluidTYPO3\Flux\Outlet\StandardOutlet');
	}

	/**
	 * @param Form\FormInterface $child
	 * @return Form\FormInterface
	 */
	public function add(Form\FormInterface $child) {
		if (FALSE === $child instanceof Form\Container\Sheet) {
			$this->last()->add($child);
		} else {
			$children = $this->children;
			foreach ($children as $existingChild) {
				if ($child->getName() === $existingChild->getName()) {
					return $this;
				}
			}
			$this->children->attach($child);
			$child->setParent($this);
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function build() {
		$dataStructArray = array(
			'meta' => array(
				'langDisable' => 1
			),
		);
		$copy = clone $this;
		foreach ($this->getSheets(TRUE) as $sheet) {
			if (FALSE === $sheet->hasChildren()) {
				$copy->remove($sheet->getName());
			}
		}
		$sheets = $copy->getSheets();
		$compactExtensionToggleOn = 0 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['compact'];
		$compactConfigurationToggleOn = 0 < $copy->getCompact();
		if (($compactExtensionToggleOn || $compactConfigurationToggleOn) && 1 === count($sheets)) {
			$dataStructArray['ROOT'] = array(
				'type' => 'array',
				'el' => $copy->last()->build(),
			);
		} elseif (0 < count($sheets)) {
			$dataStructArray['sheets'] = $copy->buildChildren();
		} else {
			$dataStructArray['ROOT'] = array(
				'type' => 'array',
				'el' => array()
			);
		}
		return $dataStructArray;
	}

	/**
	 * @param boolean $includeEmpty
	 * @return Form\Container\Sheet[]
	 */
	public function getSheets($includeEmpty = FALSE) {
		$sheets = array();
		foreach ($this->children as $sheet) {
			if (FALSE === $sheet->hasChildren() && FALSE === $includeEmpty) {
				continue;
			}
			$name = $sheet->getName();
			$sheets[$name] = $sheet;
		}
		return $sheets;
	}

	/**
	 * @return Form\FieldInterface[]
	 */
	public function getFields() {
		$fields = array();
		foreach ($this->getSheets() as $sheet) {
			$fieldsInSheet = $sheet->getFields();
			$fields = array_merge($fields, $fieldsInSheet);
		}
		return $fields;
	}

	/**
	 * @param boolean $compact
	 * @return Form\FormInterface
	 */
	public function setCompact($compact) {
		$this->compact = $compact;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getCompact() {
		return $this->compact;
	}

	/**
	 * @param boolean $enabled
	 * @return Form\FormInterface
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnabled() {
		return $this->enabled;
	}

	/**
	 * @param string $extensionName
	 * @return Form\FormInterface
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
	 * @param string $group
	 * @return Form\FormInterface
	 */
	public function setGroup($group) {
		$this->group = $group;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getGroup() {
		return $this->group;
	}

	/**
	 * @param string $icon
	 * @return Form\FormInterface
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		$icon = $this->icon;
		if (0 === strpos($icon, 'EXT:')) {
			$icon = GeneralUtility::getFileAbsFileName($icon);
		}
		return $icon;
	}

	/**
	 * @param string $id
	 * @return Form\FormInterface
	 */
	public function setId($id) {
		$allowed = 'a-z0-9_';
		$pattern = '/[^' . $allowed . ']+/i';
		if (preg_match($pattern, $id)) {
			$this->configurationService->message('Flux FlexForm with id "' . $id . '" uses invalid characters in the ID; valid characters
				are: "' . $allowed . '" and the pattern used for matching is "' . $pattern . '". This bad ID name will prevent
				you from utilising some features, fx automatic LLL reference building, but is not fatal', GeneralUtility::SYSLOG_SEVERITY_NOTICE);
		}
		$this->id = $id;
		if (TRUE === empty($this->name)) {
			$this->name = $id;
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param string $description
	 * @return Form\FormInterface
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		$description = $this->description;
		$translated = NULL;
		if (TRUE === empty($description)) {
			$extensionKey = GeneralUtility::camelCaseToLowerCaseUnderscored($this->extensionName);
			$description = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xlf:flux.' . $this->id . '.description';
		}
		if (0 === strpos($description, 'LLL:EXT:')) {
			$translated = LocalizationUtility::translate($description, $extensionKey);
		}
		return $translated !== NULL ? $translated : $description;
	}

	/**
	 * @param array $options
	 * @return Form\FormInterface
	 */
	public function setOptions(array $options) {
		$this->options = $options;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return Form\FormInterface
	 */
	public function setOption($name, $value) {
		$this->options[$name] = $value;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getOption($name) {
		return TRUE === isset($this->options[$name]) ? $this->options[$name] : NULL;
	}

	/**
	 * @return boolean
	 */
	public function hasChildren() {
		foreach ($this->children as $child) {
			if (TRUE === $child->hasChildren()) {
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param OutletInterface $outlet
	 * @return Form\FormInterface
	 */
	public function setOutlet(OutletInterface $outlet) {
		$this->outlet = $outlet;
		return $this;
	}

	/**
	 * @return OutletInterface
	 */
	public function getOutlet() {
		return $this->outlet;
	}

}
