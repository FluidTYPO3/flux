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
class Tx_Flux_Form_Wizard_Link extends Tx_Flux_Form_AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'link';

	/**
	 * @var string
	 */
	protected $type = 'popup';

	/**
	 * @var string
	 */
	protected $icon = 'link_popup.gif';

	/**
	 * @var string
	 */
	protected $script = 'wizard_add.php';

	/**
	 * @var string
	 */
	protected $activeTab = 'file';

	/**
	 * @var integer
	 */
	protected $height = 500;

	/**
	 * @var integer
	 */
	protected $width = 400;

	/**
	 * @var mixed
	 */
	protected $blindLinkOptions = '';

	/**
	 * @var mixed
	 */
	protected $blindLinkFields = '';

	/**
	 * @var mixed
	 */
	protected $allowedExtensions;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$structure = array(
			'script' => 'browse_links.php?mode=wizard&act=' . $this->getActiveTab(),
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1',
			'params' => array(
				'blindLinkOptions' => implode(',', $this->getBlindLinkOptions()),
				'blindLinkFields' => implode(',', $this->getBlindLinkFields()),
				'allowedExtensions' => implode(',', $this->getAllowedExtensions()),
			)
		);
		return $structure;
	}

	/**
	 * @param string $activeTab
	 * @return Tx_Flux_Form_Wizard_Link
	 */
	public function setActiveTab($activeTab) {
		$this->activeTab = $activeTab;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getActiveTab() {
		return $this->activeTab;
	}

	/**
	 * @param integer $height
	 * @return Tx_Flux_Form_Wizard_Link
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
	 * @return Tx_Flux_Form_Wizard_Link
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
	 * @param mixed $blindLinkOptions
	 * @return Tx_Flux_Form_Wizard_Link
	 */
	public function setBlindLinkOptions($blindLinkOptions) {
		$this->blindLinkOptions = $blindLinkOptions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBlindLinkOptions() {
		if (FALSE === is_array($this->blindLinkOptions) && FALSE === $this->blindLinkOptions instanceof Traversable) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->blindLinkOptions);
		}
		return $this->blindLinkOptions;
	}

	/**
	 * @param mixed $blindLinkFields
	 * @return Tx_Flux_Form_Wizard_Link
	 */
	public function setBlindLinkFields($blindLinkFields) {
		$this->blindLinkFields = $blindLinkFields;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBlindLinkFields() {
		if (FALSE === is_array($this->blindLinkFields) && FALSE === $this->blindLinkFields instanceof Traversable) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->blindLinkFields);
		}
		return $this->blindLinkFields;
	}

	/**
	 * @param mixed $allowedExtensions
	 * @return Tx_Flux_Form_Wizard_Link
	 */
	public function setAllowedExtensions($allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAllowedExtensions() {
		if (FALSE === is_array($this->allowedExtensions) && FALSE === $this->allowedExtensions instanceof Traversable) {
			return \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->allowedExtensions);
		}
		return $this->allowedExtensions;
	}

}
