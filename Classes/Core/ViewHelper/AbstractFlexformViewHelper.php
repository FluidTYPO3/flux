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
 * ************************************************************* */

/**
 * Base class for all FlexForm related ViewHelpers
 *
 * @package Flux
 * @subpackage Core/ViewHelper
 */
abstract class Tx_Flux_Core_ViewHelper_AbstractFlexformViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * Inject a TagBuilder
	 * (needed for compatibility w/ TYPO3 4.5 LTS where no inject method for TagBuilder exists)
	 *
	 * @param Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder Tag builder
	 * @return void
	 */
	public function injectTagBuilder(Tx_Fluid_Core_ViewHelper_TagBuilder $tagBuilder) {
		$this->tag = $tagBuilder;
	}

	/**
	 * Render method
	 */
	public function render() {
		$this->renderChildren();
		return '';
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addField($config) {
		if ($this->viewHelperVariableContainer->exists('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section') === TRUE) {
			$section = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section');
			array_push($section['fields'], $config);
			$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'section', $section);
		} else {
			$storage = $this->getStorage();
			array_push($storage['fields'], $config);
			$this->setStorage($storage);
		}
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addContentArea($config) {
		$storage = $this->getStorage();
		$row = count($storage['grid']) - 1;
		$col = count($storage['grid'][$row]) - 1;
		array_push($storage['grid'][$row][$col]['areas'], $config);
		$this->setStorage($storage);
	}

	/**
	 * @return void
	 */
	protected function addGridRow() {
		$storage = $this->getStorage();
		array_push($storage['grid'], array());
		$this->setStorage($storage);
	}

	/**
	 * @param array $config
	 * @return void
	 */
	protected function addGridColumn($config) {
		$storage = $this->getStorage();
		$row = count($storage['grid']) - 1;
		array_push($storage['grid'][$row], $config);
		$this->setStorage($storage);
	}

	/**
	 * Get the internal FCE storage array
	 * @return array
	 */
	protected function getStorage() {
		return $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage');
	}

	/**
	 * Set the internal FCE storage array
	 * @param a $storage
	 * @return void
	 */
	protected function setStorage($storage) {
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $storage);
	}

}

?>
