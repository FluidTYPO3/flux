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

	public function getComponent() {
		/** @var Edit $component */
		$component = $this->getPreparedComponent('Edit');
		$component->setOpenOnlyIfSelected($this->arguments['openOnlyIfSelected']);
		$component->setHeight($this->arguments['height']);
		$component->setWidth($this->arguments['width']);
		return $component;
	}

}
