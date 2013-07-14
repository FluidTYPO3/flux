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
 * @subpackage Form\Field
 */
class Tx_Flux_Form_Field_Input extends Tx_Flux_Form_AbstractFormField implements Tx_Flux_Form_FieldInterface {

	/**
	 * @var string
	 */
	protected $validate;

	/**
	 * @var integer
	 */
	protected $size = 32;

	/**
	 * @var integer
	 */
	protected $maxCharacters;

	/**
	 * @var integer
	 */
	protected $minimum;

	/**
	 * @var integer
	 */
	protected $maximum;

	/**
	 * @var string
	 */
	protected $placeholder;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$minimum = $this->getMinimum();
		$maximum = $this->getMaximum();
		$validate = $this->getValidate();
		$fieldConfiguration = array(
			'type' => 'input',
			'transform' => $this->getTransform(),
			'placeholder' => $this->getPlaceholder(),
			'size' => $this->getSize(),
			'default' => $this->getDefault(),
			'max' => $this->getMaxCharacters(),
			'eval' => $validate,
		);
		if ($minimum >= 0 || $maximum >= 0 && in_array('int', t3lib_div::trimExplode(',', $validate))) {
			$fieldConfiguration['range'] = array(
				'lower' => $minimum,
				'upper' => $maximum,
			);
		}
		return $fieldConfiguration;
	}

	/**
	 * @param integer $maxCharacters
	 */
	public function setMaxCharacters($maxCharacters) {
		$this->maxCharacters = $maxCharacters;
	}

	/**
	 * @return integer
	 */
	public function getMaxCharacters() {
		return $this->maxCharacters;
	}

	/**
	 * @param integer $maximum
	 */
	public function setMaximum($maximum) {
		$this->maximum = $maximum;
	}

	/**
	 * @return integer
	 */
	public function getMaximum() {
		return $this->maximum;
	}

	/**
	 * @param integer $minimum
	 */
	public function setMinimum($minimum) {
		$this->minimum = $minimum;
	}

	/**
	 * @return integer
	 */
	public function getMinimum() {
		return $this->minimum;
	}

	/**
	 * @param string $placeholder
	 */
	public function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;
	}

	/**
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * @param integer $size
	 */
	public function setSize($size) {
		$this->size = $size;
	}

	/**
	 * @return integer
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @param string $validate
	 */
	public function setValidate($validate) {
		$this->validate = $validate;
	}

	/**
	 * @return string
	 */
	public function getValidate() {
		return $this->validate;
	}

}
