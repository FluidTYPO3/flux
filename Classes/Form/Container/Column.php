<?php
namespace FluidTYPO3\Flux\Form\Container;
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

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Column extends AbstractFormContainer implements ContainerInterface {

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
	 * @return Content[]
	 */
	public function getAreas() {
		return (array) iterator_to_array($this->children);
	}

	/**
	 * @param integer $colspan
	 * @return Column
	 */
	public function setColspan($colspan) {
		$this->colspan = $colspan;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getColspan() {
		return $this->colspan;
	}

	/**
	 * @param integer $columnPosition
	 * @return Column
	 */
	public function setColumnPosition($columnPosition) {
		$this->columnPosition = $columnPosition;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getColumnPosition() {
		return $this->columnPosition;
	}

	/**
	 * @param integer $rowspan
	 * @return Column
	 */
	public function setRowspan($rowspan) {
		$this->rowspan = $rowspan;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getRowspan() {
		return $this->rowspan;
	}

	/**
	 * @param string $style
	 * @return Column
	 */
	public function setStyle($style) {
		$this->style = $style;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStyle() {
		return $this->style;
	}

}
