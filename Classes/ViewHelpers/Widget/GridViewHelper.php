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

require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('fluid', 'Classes/Core/Widget/AbstractWidgetViewHelper.php');

/**
 * Grid Widget for rendering a grid in previews of BE elements
 *
 * @package Flux
 * @subpackage ViewHelpers/Widget
 */
class Tx_Flux_ViewHelpers_Widget_GridViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper {

	/**
	 * @var Tx_Flux_ViewHelpers_Widget_Controller_GridController
	 */
	protected $controller;

	/**
	 * @param Tx_Flux_ViewHelpers_Widget_Controller_GridController $controller
	 * @return void
	 */
	public function injectController(Tx_Flux_ViewHelpers_Widget_Controller_GridController $controller) {
		$this->controller = $controller;
	}

	/**
	 * @return string
	 */
	public function render() {
		if ($this->templateVariableContainer->exists('grid')) {
			$this->controller->setGrid($this->templateVariableContainer->get('grid'));
		}
		if ($this->templateVariableContainer->exists('row')) {
			$this->controller->setRow($this->templateVariableContainer->get('row'));
		}
		return $this->initiateSubRequest();
	}

}
