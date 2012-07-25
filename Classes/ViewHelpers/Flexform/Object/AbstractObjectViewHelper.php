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
 * Base class for Flexform Object ViewHelpers
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Object
 */
class Tx_Flux_ViewHelpers_Flexform_Object_AbstractObjectViewHelper extends Tx_Fluid_Core_Widget_AbstractWidgetViewHelper {

	/**
	 * Initialize arguments
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the field', TRUE);
		$this->registerArgument('label', 'string', 'Label of the field, can be an LLL: file path', FALSE);
	}

	/**
	 * @var Tx_Flux_ViewHelpers_Flexform_Object_Controller_StandardObjectController
	 */
	protected $controller;

	/**
	 * @param Tx_Flux_ViewHelpers_Flexform_Object_Controller_StandardObjectController $controller
	 * @return void
	 */
	public function injectController(Tx_Flux_ViewHelpers_Flexform_Object_Controller_StandardObjectController $controller) {
		$this->controller = $controller;
	}

	/**
	 * @return Tx_Extbase_MVC_ResponseInterface
	 */
	public function render() {
		$this->controller->setObjectType(str_replace('ViewHelper', '', array_pop(explode('_', get_class($this)))));
		$this->controller->setTemplateVariableContainer($this->templateVariableContainer);
		$this->controller->setViewHelperVariableContainer($this->viewHelperVariableContainer);
		$this->initiateSubRequest();
	}
}
