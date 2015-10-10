<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * Edit
 */
class Edit extends AbstractWizard {

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
	 * @var array
	 */
	protected $module = array(
		'name' => 'wizard_edit'
	);

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
			'title' => $this->getLabel(),
			'icon' => $this->icon,
			'popup_onlyOpenIfSelected' => intval($this->getOpenOnlyIfSelected()),
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1'
		);
		return $configuration;
	}

	/**
	 * @param boolean $openOnlyIfSelected
	 * @return Edit
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
	 * @return Edit
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
	 * @return Edit
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
