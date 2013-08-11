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
 */
class Tx_Flux_Form extends Tx_Flux_Form_AbstractFormContainer implements Tx_Flux_Form_FieldContainerInterface {

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
	protected $extensionName = 'Flux';

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
	 * @param array $settings
	 * @return Tx_Flux_Form
	 */
	public static function create(array $settings) {
		$form = parent::create($settings);
		if (TRUE === isset($settings['sheets'])) {
			foreach ($settings['sheets'] as $sheetName => $sheetSettings) {
				if (FALSE === isset($sheetSettings['name'])) {
					$sheetSettings['name'] = $sheetName;
				}
				$sheet = Tx_Flux_Form_Container_Sheet::create($sheetSettings);
				$form->add($sheet);
			}
		}
		return $form;
	}

	/**
	 * @return void
	 */
	public function initializeObject() {
		/** @var Tx_Flux_Form_Container_Sheet $defaultSheet */
		$defaultSheet = $this->objectManager->get('Tx_Flux_Form_Container_Sheet');
		$defaultSheet->setName('options');
		$defaultSheet->setLabel(Tx_Extbase_Utility_Localization::translate('tt_content.tx_flux_options', 'Flux'));
		$this->add($defaultSheet);
	}

	/**
	 * @param Tx_Flux_Form_FormInterface $child
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function add(Tx_Flux_Form_FormInterface $child) {
		if (FALSE === $child instanceof Tx_Flux_Form_Container_Sheet) {
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
			if (0 === count($sheet->getFields())) {
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
	 * @return Tx_Flux_Form_Container_Sheet[]
	 */
	public function getSheets($includeEmpty = FALSE) {
		$sheets = array();
		foreach ($this->children as $index => $sheet) {
			if (0 === count($sheet->getFields()) && FALSE === $includeEmpty) {
				continue;
			}
			$sheets[$index] = $sheet;
		}
		return $sheets;
	}

	/**
	 * @return Tx_Flux_Form_FieldInterface[]
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
	 * @return Tx_Flux_Form_FormInterface
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
	 * @return Tx_Flux_Form_FormInterface
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
	 * @return Tx_Flux_Form_FormInterface
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
	 * @return Tx_Flux_Form_FormInterface
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
	 * @return Tx_Flux_Form_FormInterface
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
			$icon = t3lib_div::getFileAbsFileName($icon);
		}
		return $icon;
	}

	/**
	 * @param string $id
	 * @return Tx_Flux_Form_FormInterface
	 */
	public function setId($id) {
		$allowed = 'a-z';
		$pattern = '/[^' . $allowed . ']+/i';
		if (preg_match($pattern, $id)) {
			$this->configurationService->message('Flux FlexForm with id "' . $id . '" uses invalid characters in the ID; valid characters
				are: "' . $allowed . '" and the pattern used for matching is "' . $pattern . '". This bad ID name will prevent
				you from utilising some features, fx automatic LLL reference building, but is not fatal', t3lib_div::SYSLOG_SEVERITY_NOTICE);
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
	 * @return Tx_Flux_Form_FormInterface
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
		if (TRUE === empty($description)) {
			$extensionKey = t3lib_div::camelCaseToLowerCaseUnderscored($this->extensionName);
			$description = 'LLL:EXT:' . $extensionKey . '/Resources/Private/Language/locallang.xml:flux.' . $this->id . '.description';
		}
		return $description;
	}

}
