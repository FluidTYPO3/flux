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

	/**
	 * @var string
	 */
	protected $parentField;

	/**
	 * @var boolean
	 */
	protected $expandAll = FALSE;

	/**
	 * @var boolean
	 */
	protected $showHeader = TRUE;

	/**
	 * @var integer
	 */
	protected $width = 250;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('select');
		$configuration['renderMode'] = 'tree';
		$configuration['treeConfig'] = array(
			'parentField' => $this->getParentField(),
			'expandAll' => $this->getExpandAll(),
			'showHeader' => $this->getShowHeader(),
			'width' => $this->getWidth()
		);
		return $configuration;
	}

	/**
	 * @param boolean $expandAll
	 */
	public function setExpandAll($expandAll) {
		$this->expandAll = $expandAll;
	}

	/**
	 * @return boolean
	 */
	public function getExpandAll() {
		return $this->expandAll;
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

}
