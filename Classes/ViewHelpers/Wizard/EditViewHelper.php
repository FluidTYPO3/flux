<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Edit;

/**
 * Field Wizard: Edit
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
class EditViewHelper extends AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 580);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 580);
		$this->registerArgument('openOnlyIfSelected', 'boolean', 'Only open the edit dialog if an item is selected', FALSE, TRUE);
	}

	/**
	 * @return Edit
	 */
	public function getComponent() {
		/** @var Edit $component */
		$component = $this->getPreparedComponent('Edit');
		$component->setOpenOnlyIfSelected($this->arguments['openOnlyIfSelected']);
		$component->setHeight($this->arguments['height']);
		$component->setWidth($this->arguments['width']);
		return $component;
	}

}
