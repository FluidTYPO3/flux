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
