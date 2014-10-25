<?php
namespace FluidTYPO3\Flux\Package;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 *****************************************************************/

use FluidTYPO3\Flux\Collection\CollectableInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class PluginDefinition
 *
 * Defines one plugin which can be used by TYPO3.
 */
class PluginDefinition implements CollectableInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $label;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var array
	 */
	protected $controllerActions;

	/**
	 * @var array
	 */
	protected $uncachedControllerActions;

	/**
	 * @var boolean
	 */
	protected $insertable = TRUE;

	/**
	 * @var boolean
	 */
	protected $asContentType = FALSE;

	/**
	 * @param string $name
	 * @param string $label
	 * @param string $icon
	 * @param array $actions
	 * @param array $uncachedActions
	 */
	public function __construct($name, $label, $icon = NULL, array $actions = NULL, array $uncachedActions = NULL) {
		$this->name = $name;
		$this->label = $label;
		$this->icon = $icon !== NULL ? $icon : GeneralUtility::getFileAbsFileName('EXT:flux/ext_icon.gif');
		$this->controllerActions = $actions;
		$this->uncachedControllerActions = $uncachedActions;
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @return array
	 */
	public function getControllerActions() {
		return $this->controllerActions;
	}

	/**
	 * @return array
	 */
	public function getUncachedControllerActions() {
		return $this->uncachedControllerActions;
	}

	/**
	 * @return boolean
	 */
	public function getInsertable() {
		return $this->insertable;
	}

	/**
	 * @param boolean $insertable
	 * @return void
	 */
	public function setInsertable($insertable) {
		$this->insertable = $insertable;
	}

	/**
	 * @return boolean
	 */
	public function getAsContentType() {
		return $this->asContentType;
	}

	/**
	 * @param boolean $asContentType
	 * @return void
	 */
	public function setAsContentType($asContentType) {
		$this->asContentType = $asContentType;
	}

}
