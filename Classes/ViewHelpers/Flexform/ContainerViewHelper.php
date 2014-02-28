<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * ### FlexForm Field Container element
 *
 * Use around other Flux fields to make these fields nested visually
 * and in variable scopes (i.e. a field called "name" inside a palette
 * called "person" would end up with "person" being an array containing
 * the "name" property, rendered as {person.name} in Fluid.
 *
 * The field grouping can be hidden or completely removed. In this regard
 * this element is a simpler version of the Section and Object logic.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_ContainerViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for the attribute, can be LLL: value. Optional - if not specified, Flux ' .
			'tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension ' .
			'rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where ' .
			'"foobar" is the name of the field.', FALSE, NULL);
	}

	/**
	 * @var array<Tx_Flux_ViewHelpers_Field_AbstractFieldViewHelper>
	 */
	protected $childInstances;

	/**
	 * @return NULL
	 */
	public function render() {
		$this->viewHelperVariableContainer->addOrUpdate('Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper', 'insidePalette', array());
		$this->renderChildren();
		$this->childInstances = $this->viewHelperVariableContainer->get('Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper', 'insidePalette');
		$this->configuration = array(
			'name' => $this->getName()
		);
		$this->structure = $this->createStructure();
		$this->viewHelperVariableContainer->remove('Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper', 'insidePalette');
		$this->addField();
		return NULL;
	}

	/**
	 * @return array
	 */
	public function createStructure() {
		$elements = array();
		foreach ($this->childInstances as $childNode) {
			$elementName = $childNode->getName();
			$configuration = $childNode->createStructure();
			$elements[$elementName] = $configuration;
		}
		$structure = array(
			'type' => 'array',
			'el' => $elements,
			'title' => $this->getLabel(),
		);
		return $structure;
	}

}
