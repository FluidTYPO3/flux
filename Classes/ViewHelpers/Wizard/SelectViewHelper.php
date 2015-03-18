<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Select;

/**
 * Field Wizard: Edit
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
class SelectViewHelper extends AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('mode', 'string', 'Selection mode - substitution, append or prepend', FALSE, 'substitution');
		$this->registerArgument('items', 'mixed', 'Comma-separated, comma-and-semicolon-separated or array list of possible values', TRUE);
	}

	/**
	 * @return Select
	 */
	public function getComponent() {
		/** @var Select $component */
		$component = $this->getPreparedComponent('Select');
		$component->setMode($this->arguments['mode']);
		$component->setItems($this->arguments['items']);
		return $component;
	}

}
