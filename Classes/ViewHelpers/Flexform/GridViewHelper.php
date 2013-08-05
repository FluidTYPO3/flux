<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Grid container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_GridViewHelper extends Tx_Flux_ViewHelpers_AbstractFlexformViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Optional name of this grid - defaults to "grid"', FALSE, 'grid');
		$this->registerArgument('label', 'string', 'Optional label for this grid - defaults to an LLL value (reported if it is missing)', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$grid = $this->getGrid($this->arguments['name']);
		$grid->setParent($this->getForm());
		$grid->setLabel($this->arguments['label']);
		$container = $this->getContainer();
		$this->setContainer($grid);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
