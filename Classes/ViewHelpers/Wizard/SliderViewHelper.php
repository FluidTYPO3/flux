<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;
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
