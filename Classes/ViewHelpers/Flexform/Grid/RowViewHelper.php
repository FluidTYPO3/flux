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
class Tx_Flux_ViewHelpers_Flexform_Grid_RowViewHelper extends Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('repeat', 'integer', 'number of times to repeat this colum while appending $iteration to name', FALSE, 1);
		$this->registerArgument('iteration', 'string', 'name of the variable to store iteration information (index, cycle, isFirst, isLast, isEven, isOdd)');
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$iterationData = array(
			'index' => 0,
			'cycle' => 1,
			'total' => $this->arguments['repeat']
		);

		for ($i=0; $i<$this->arguments['repeat']; $i++) {
			if ($this->arguments['iteration'] !== NULL) {
				$iterationData['isFirst'] = $iterationData['cycle'] === 1;
				$iterationData['isLast'] = $iterationData['cycle'] === $iterationData['total'];
				$iterationData['isEven'] = $iterationData['cycle'] % 2 === 0;
				$iterationData['isOdd'] = !$iterationData['isEven'];
				$this->templateVariableContainer->add($this->arguments['iteration'], $iterationData);
				$iterationData['index'] ++;
				$iterationData['cycle'] ++;
			}
			$this->addGridRow();
			$this->renderChildren();
			if ($this->arguments['iteration'] !== NULL) {
				$this->templateVariableContainer->remove($this->arguments['iteration']);
			}
		}
		return '';
	}

}
