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
abstract class Tx_Flux_Form_AbstractMultiValueFormField extends Tx_Flux_Form_AbstractFormField implements Tx_Flux_Form_MultiValueFieldInterface {

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
	 * @param string $type
	 * @return array
	 */
	public function prepareConfiguration($type) {
		$configuration = parent::prepareConfiguration($type);
		$configuration['size'] = $this->getSize();
		$configuration['maxitems'] = $this->getMaxItems();
		$configuration['minitems'] = $this->getMinItems();
		$configuration['multiple'] = $this->getMultiple();
		$configuration['itemListStyle'] = $this->getItemListStyle();
		$configuration['selectedListStyle'] = $this->getSelectedListStyle();
		return $configuration;
	}

	/**
	 * @param integer $size
	 * @return Tx_Flux_Form_MultiValueFieldInterface
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
	 */
	public function setMultiple($multiple) {
		$this->multiple = $multiple;
	}

	/**
	 * @return boolean
	 */
	public function getMultiple() {
		return $this->multiple;
	}

	/**
	 * @param integer $maxItems
	 * @return Tx_Flux_Form_MultiValueFieldInterface
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
	 * @return Tx_Flux_Form_MultiValueFieldInterface
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
	 * @return Tx_Flux_Form_MultiValueFieldInterface
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
	 * @return Tx_Flux_Form_MultiValueFieldInterface
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

}
