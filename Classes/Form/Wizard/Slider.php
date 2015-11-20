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
 * Slider
 */
class Slider extends AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'slider';

	/**
	 * @var string
	 */
	protected $type = 'slider';

	/**
	 * @var string
	 */
	protected $icon = NULL;

	/**
	 * @var array
	 */
	protected $module = NULL;

	/**
	 * @var integer
	 */
	protected $width = 400;

	/**
	 * @var integer
	 */
	protected $step;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = array(
			'width' => $this->getWidth(),
			'step' => $this->getStep(),
		);
		return $configuration;
	}

	/**
	 * @param integer $step
	 * @return Slider
	 */
	public function setStep($step) {
		$this->step = $step;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getStep() {
		return $this->step;
	}

	/**
	 * @param integer $width
	 * @return Slider
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
