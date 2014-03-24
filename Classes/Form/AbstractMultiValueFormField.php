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

/**
 * @package Flux
 * @subpackage Form
 */
abstract class AbstractMultiValueFormField extends AbstractFormField implements MultiValueFieldInterface {

	/**
	 * @var integer
	 */
	protected $size = 1;

	/**
	 * @var boolean
	 */
	protected $multiple = FALSE;

	/**
	 * @var integer
	 */
	protected $minItems = 0;

	/**
	 * @var integer
	 */
	protected $maxItems;

	/**
	 * @var string
	 */
	protected $itemListStyle;

	/**
	 * @var string
	 */
	protected $selectedListStyle;

	/**
	 * @var string
	 */
	protected $renderMode = 'default';

	/**
	 * @param string $type
	 * @return array
	 */
	public function prepareConfiguration($type) {
		$configuration = parent::prepareConfiguration($type);
		$configuration['size'] = $this->getSize();
		$configuration['maxitems'] = $this->getMaxItems();
		$configuration['minitems'] = $this->getMinItems();
		$configuration['multiple'] = $this->getMultiple();
		$configuration['renderMode'] = $this->getRenderMode();
		$configuration['itemListStyle'] = $this->getItemListStyle();
		$configuration['selectedListStyle'] = $this->getSelectedListStyle();
		return $configuration;
	}

	/**
	 * @param integer $size
	 * @return MultiValueFieldInterface
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @param boolean $multiple
	 * @return MultiValueFieldInterface
	 */
	public function setMultiple($multiple) {
		$this->multiple = $multiple;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getMultiple() {
		return $this->multiple;
	}

	/**
	 * @param integer $maxItems
	 * @return MultiValueFieldInterface
	 */
	public function setMaxItems($maxItems) {
		$this->maxItems = $maxItems;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaxItems() {
		return $this->maxItems;
	}

	/**
	 * @param integer $minItems
	 * @return MultiValueFieldInterface
	 */
	public function setMinItems($minItems) {
		$this->minItems = $minItems;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMinItems() {
		return $this->minItems;
	}

	/**
	 * @param string $itemListStyle
	 * @return MultiValueFieldInterface
	 */
	public function setItemListStyle($itemListStyle) {
		$this->itemListStyle = $itemListStyle;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getItemListStyle() {
		return $this->itemListStyle;
	}

	/**
	 * @param string $selectedListStyle
	 * @return MultiValueFieldInterface
	 */
	public function setSelectedListStyle($selectedListStyle) {
		$this->selectedListStyle = $selectedListStyle;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSelectedListStyle() {
		return $this->selectedListStyle;
	}

	/**
	 * @param string $renderMode
	 * @return MultiValueFieldInterface
	 */
	public function setRenderMode($renderMode) {
		$this->renderMode = $renderMode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRenderMode() {
		return $this->renderMode;
	}

}
