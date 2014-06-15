<?php
namespace FluidTYPO3\Flux\Form\Field;
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

use FluidTYPO3\Flux\Form\AbstractRelationFormField;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class Tree extends AbstractRelationFormField {

	const DEFAULT_ALLOW_RECURSIVE_MODE = FALSE;
	const DEFAULT_EXPAND_ALL = FALSE;
	const DEFAULT_NON_SELECTABLE_LEVELS  = '0';
	const DEFAULT_MAX_LEVELS = 2;
	const DEFAULT_SHOW_HEADER = FALSE;
	const DEFAULT_WIDTH = 280;

	/**
	 * @var string
	 */
	protected $parentField;

	/**
	 * @var boolean
	 */
	protected $allowRecursiveMode = self::DEFAULT_ALLOW_RECURSIVE_MODE;

	/**
	 * @var boolean
	 */
	protected $expandAll = self::DEFAULT_EXPAND_ALL;

	/**
	 * @var string
	 */
	protected $nonSelectableLevels = self::DEFAULT_NON_SELECTABLE_LEVELS;

	/**
	 * @var integer
	 */
	protected $maxLevels = self::DEFAULT_MAX_LEVELS;

	/**
	 * @var boolean
	 */
	protected $showHeader = self::DEFAULT_SHOW_HEADER;

	/**
	 * @var integer
	 */
	protected $width = self::DEFAULT_WIDTH;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('select');
		$configuration['renderMode'] = 'tree';
		$configuration['treeConfig'] = array(
			'parentField' => $this->getParentField(),
			'appearance' => array (
				'allowRecursiveMode' => $this->getAllowRecursiveMode(),
				'expandAll' => $this->getExpandAll(),
				'nonSelectableLevels' => $this->getNonSelectableLevels(),
				'maxLevels' => $this->getMaxLevels(),
				'showHeader' => $this->getShowHeader(),
				'width' => $this->getWidth(),
			),
		);
		return $configuration;
	}

	/**
	 * @param string $parentField
	 * @return Tree
	 */
	public function setParentField($parentField) {
		$this->parentField = $parentField;
		return $this;
	}

	/**
	* @return string
	*/
	public function getParentField() {
		return $this->parentField;
	}

	/**
	 * @param boolean $allowRecursiveMode
	 * @return Tree
	 */
	public function setAllowRecursiveMode($allowRecursiveMode) {
		$this->allowRecursiveMode = $allowRecursiveMode;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getAllowRecursiveMode() {
		return $this->allowRecursiveMode;
	}

	/**
	 * @param boolean $expandAll
	 * @return Tree
	 */
	public function setExpandAll($expandAll) {
		$this->expandAll = $expandAll;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getExpandAll() {
		return $this->expandAll;
	}

	/**
	 * @param string $nonSelectableLevels
	 * @return Tree
	 */
	public function setNonSelectableLevels($nonSelectableLevels) {
		$this->nonSelectableLevels = $nonSelectableLevels;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getNonSelectableLevels() {
		return $this->nonSelectableLevels;
	}

	/**
	 * @param integer $maxLevels
	 * @return Tree
	 */
	public function setMaxLevels($maxLevels) {
		$this->maxLevels = $maxLevels;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaxLevels() {
		return $this->maxLevels;
	}

	/**
	 * @param boolean $showHeader
	 * @return Tree
	 */
	public function setShowHeader($showHeader) {
		$this->showHeader = $showHeader;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowHeader() {
		return $this->showHeader;
	}

	/**
	 * @param integer $width
	 * @return Tree
	 */
	public function setWidth($width) {
		$this->width = $width;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getWidth() {
		return $this->width;
	}


}
