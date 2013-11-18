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
		$configuration = $this->prepareConfiguration('input');
		$configuration['placeholder'] = $this->getPlaceholder();
		$configuration['size'] = $this->getSize();
		$configuration['max'] = $this->getMaxCharacters();
		$configuration['eval'] = $validate;
		if ($minimum >= 0 || $maximum >= 0 && in_array('int', \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $validate))) {
			$configuration['range'] = array(
				'lower' => $minimum,
				'upper' => $maximum,
			);
		}
		return $configuration;
	}

	/**
	 * @param integer $maxCharacters
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function setMaxCharacters($maxCharacters) {
		$this->maxCharacters = $maxCharacters;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaxCharacters() {
		return $this->maxCharacters;
	}

	/**
	 * @param integer $maximum
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function setMaximum($maximum) {
		$this->maximum = $maximum;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaximum() {
		return $this->maximum;
	}

	/**
	 * @param integer $minimum
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function setMinimum($minimum) {
		$this->minimum = $minimum;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMinimum() {
		return $this->minimum;
	}

	/**
	 * @param string $placeholder
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPlaceholder() {
		return $this->placeholder;
	}

	/**
	 * @param integer $size
	 * @return Tx_Flux_Form_Field_Input
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * @param string $validate
	 * @return Tx_Flux_Form_Field_Input
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
				$validators = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->validate);
				array_push($validators, 'required');
				$validate = implode(',', $validators);
			}
		}
		return $validate;
	}

}
