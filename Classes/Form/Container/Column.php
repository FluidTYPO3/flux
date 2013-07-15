<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Tx_Flux_Form_Container_Column extends Tx_Flux_Form_AbstractFormContainer implements Tx_Flux_Form_ContainerInterface, Tx_Flux_Form_FieldContainerInterface {

	/**
	 * @var integer
	 */
	protected $columnPosition = 0;

	/**
	 * @var integer
	 */
	protected $colspan = 1;

	/**
	 * @var integer
	 */
	protected $rowspan = 1;

	/**
	 * @var string
	 */
	protected $style = NULL;

	/**
	 * @return array
	 */
	public function build() {
		$structure = array(
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'colspan' => $this->getColspan(),
			'rowspan' => $this->getRowspan(),
			'style' => $this->getStyle(),
			'colPos' => $this->getColumnPosition(),
			'areas' => $this->buildChildren()
		);
		return $structure;
	}

	/**
	 * @return Tx_Flux_Form_Container_Content[]
	 */
	public function getAreas() {
		return $this->children;
	}

	/**
	 * @param int $colspan
	 */
	public function setColspan($colspan) {
		$this->colspan = $colspan;
	}

	/**
	 * @return int
	 */
	public function getColspan() {
		return $this->colspan;
	}

	/**
	 * @param int $columnPosition
	 */
	public function setColumnPosition($columnPosition) {
		$this->columnPosition = $columnPosition;
	}

	/**
	 * @return int
	 */
	public function getColumnPosition() {
		return $this->columnPosition;
	}

	/**
	 * @param int $rowspan
	 */
	public function setRowspan($rowspan) {
		$this->rowspan = $rowspan;
	}

	/**
	 * @return int
	 */
	public function getRowspan() {
		return $this->rowspan;
	}

	/**
	 * @param string $style
	 */
	public function setStyle($style) {
		$this->style = $style;
	}

	/**
	 * @return string
	 */
	public function getStyle() {
		return $this->style;
	}

}
