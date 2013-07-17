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
 * @subpackage Form\Wizard
 */
class Tx_Flux_Form_Wizard_Edit extends Tx_Flux_Form_AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'edit';

	/**
	 * @var string
	 */
	protected $type = 'script';

	/**
	 * @var string
	 */
	protected $icon = 'edit2.gif';

	/**
	 * @var string
	 */
	protected $script = 'wizard_edit.php';

	/**
	 * @var boolean
	 */
	protected $openOnlyIfSelected = TRUE;

	/**
	 * @var integer
	 */
	protected $width = 450;

	/**
	 * @var integer
	 */
	protected $height = 720;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = array(
			'type' => 'popup',
			'title' => $this->arguments['title'],
			'icon' => $this->icon,
			'script' => $this->script,
			'popup_onlyOpenIfSelected' => intval($this->getOpenOnlyIfSelected()),
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1'
		);
		return $configuration;
	}

	/**
	 * @param boolean $openOnlyIfSelected
	 * @return Tx_Flux_Form_Wizard_Edit
	 */
	public function setOpenOnlyIfSelected($openOnlyIfSelected) {
		$this->openOnlyIfSelected = $openOnlyIfSelected;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getOpenOnlyIfSelected() {
		return $this->openOnlyIfSelected;
	}

	/**
	 * @param integer $height
	 * @return Tx_Flux_Form_Wizard_Edit
	 */
	public function setHeight($height) {
		$this->height = $height;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param integer $width
	 * @return Tx_Flux_Form_Wizard_Edit
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
