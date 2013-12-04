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
 * Flexform Grid Row ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Grid
 */
class Tx_Flux_ViewHelpers_Flexform_Grid_RowViewHelper extends Tx_Flux_ViewHelpers_AbstractFlexformViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Optional name of this row - defaults to "row"', FALSE, 'row');
		$this->registerArgument('label', 'string', 'Optional label for this row - defaults to an LLL value (reported if it is missing)', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$name = ('row' === $this->arguments['name'] ? uniqid('row') : $this->arguments['name']);
		$row = $this->getForm()->createContainer('Row', $name, $this->arguments['label']);
		$container = $this->getContainer();
		$container->add($row);
		$this->setContainer($row);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
