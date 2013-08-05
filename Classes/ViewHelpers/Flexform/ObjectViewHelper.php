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
 *****************************************************************/

/**
 * FlexForm field section object ViewHelper
 *
 * Use this inside flux:flexform.section to name and divide the fields
 * into individual objects that can be inserted into the section.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_ObjectViewHelper extends Tx_Flux_ViewHelpers_AbstractFlexformViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the section object, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for section object, can be LLL: value. Optional - if not specified, ' .
			'Flux tries to detect an LLL label named "flux.fluxFormId.objects.foobar" based on object name, in scope of ' .
			'extension rendering the Flux form.', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		/** @var Tx_Flux_Form_Container_Section $object */
		$object = $this->objectManager->get('Tx_Flux_Form_Container_Object');
		$object->setName($this->arguments['name']);
		$object->setLabel($this->arguments['label']);
		$container = $this->getContainer();
		$container->add($object);
		$this->setContainer($object);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
