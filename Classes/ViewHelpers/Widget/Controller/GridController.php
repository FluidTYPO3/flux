<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Grid Widget Controller
 *
 * @package Flux
 * @subpackage ViewHelpers/Widget
 */
class Tx_Flux_ViewHelpers_Widget_Controller_GridController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $grid = array();

	/**
	 * @var array
	 */
	protected $row = array();

	/**
	 * @param array $grid
	 * @return void
	 */
	public function setGrid($grid) {
		$this->grid = $grid;
	}

	/**
	 * @param array $row
	 * @return void
	 */
	public function setRow($row) {
		$this->row = $row;
	}

	/**
	 * @return void
	 */
	protected function assignGridVariables() {
		foreach ($this->grid as $index => $columns) {
			$this->grid[$index]['totalColumnCount'] = array();
			foreach ($columns as $columnIndex => $column) {
				$add = (1 + ($column['colspan'] - 1));
				for ($i = 0; $i < $add; $i++) {
					array_push($this->grid[$index]['totalColumnCount'], 1);
				}
				if (isset($column['areas']) === TRUE) {
					foreach ($column['areas'] as $areaIndex => $area) {
						$this->grid[$index][$columnIndex]['areas'][$areaIndex]['md5'] = md5(implode('', $this->row) . $area['name']);
					}
				}
				$this->grid[$index][$columnIndex]['md5'] = md5($column['name']);
			}
		}
	}

	/**
	 * @return string
	 */
	public function indexAction() {
		$this->assignGridVariables();
		$this->view->assign('grid', $this->grid);
		$this->view->assign('row', $this->row);
		if (Tx_Flux_Utility_Version::assertCoreVersionIsBelowSixPointZero() === TRUE) {
			return $this->view->render('legacy');
		}
	}
}
