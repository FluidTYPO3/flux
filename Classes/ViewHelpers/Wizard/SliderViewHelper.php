<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Slider;

/**
 * Field Wizard: Slider
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
class SliderViewHelper extends AbstractWizardViewHelper {

	/**
	 * @var string
	 */
	protected $label = 'Slider';

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('step', 'integer', 'Increment slider values by this much for each step', FALSE, 1);
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 32);
	}

	/**
	 * @return Slider
	 */
	public function getComponent() {
		/** @var Slider $component */
		$component = $this->getPreparedComponent('Slider');
		$component->setWidth($this->arguments['width']);
		$component->setStep($this->arguments['step']);
		return $component;
	}

}
