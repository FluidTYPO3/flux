<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Link
 */
class Link extends AbstractWizard {

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
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1',
			'params' => array(
				'blindLinkOptions' => implode(',', $this->getBlindLinkOptions()),
				'blindLinkFields' => implode(',', $this->getBlindLinkFields()),
				'allowedExtensions' => implode(',', $this->getAllowedExtensions())
			),
			'module' => array(
				'name' => 'wizard_element_browser',
				'urlParameters' => array(
					'mode' => 'wizard',
					'act' => $this->getActiveTab()
				)
			)
		);

		return $structure;
	}

	/**
	 * @param string $activeTab
	 * @return Link
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
	 * @return Link
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
	 * @return Link
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
	 * @return Link
	 */
	public function setBlindLinkOptions($blindLinkOptions) {
		$this->blindLinkOptions = $blindLinkOptions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBlindLinkOptions() {
		if (FALSE === is_array($this->blindLinkOptions) && FALSE === $this->blindLinkOptions instanceof \Traversable) {
			return GeneralUtility::trimExplode(',', $this->blindLinkOptions);
		}
		return $this->blindLinkOptions;
	}

	/**
	 * @param mixed $blindLinkFields
	 * @return Link
	 */
	public function setBlindLinkFields($blindLinkFields) {
		$this->blindLinkFields = $blindLinkFields;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBlindLinkFields() {
		if (FALSE === is_array($this->blindLinkFields) && FALSE === $this->blindLinkFields instanceof \Traversable) {
			return GeneralUtility::trimExplode(',', $this->blindLinkFields);
		}
		return $this->blindLinkFields;
	}

	/**
	 * @param mixed $allowedExtensions
	 * @return Link
	 */
	public function setAllowedExtensions($allowedExtensions) {
		$this->allowedExtensions = $allowedExtensions;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAllowedExtensions() {
		if (FALSE === is_array($this->allowedExtensions) && FALSE === $this->allowedExtensions instanceof \Traversable) {
			return GeneralUtility::trimExplode(',', $this->allowedExtensions);
		}
		return $this->allowedExtensions;
	}

}
