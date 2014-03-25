<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Base class for all FlexForm fields.
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
abstract class AbstractFieldViewHelper extends AbstractFormViewHelper {

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
		$this->registerArgument('default', 'string', 'Default value for this attribute');
		$this->registerArgument('required', 'boolean', 'If TRUE, this attribute must be filled when editing the FCE', FALSE, FALSE);
		$this->registerArgument('exclude', 'boolean', 'If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)', FALSE, FALSE);
		$this->registerArgument('transform', 'string', 'Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Tx_MyExt_Domain_Model_Object or ObjectStorage with type hint. Also supported are FED Resource classes.');
		$this->registerArgument('enabled', 'boolean', 'If FALSE, disables the field in the FlexForm', FALSE, TRUE);
		$this->registerArgument('requestUpdate', 'boolean', 'If TRUE, the form is force-saved and reloaded when field value changes', FALSE, FALSE);
		$this->registerArgument('displayCond', 'string', 'Optional "Display Condition" (TCA style) for this particular field', FALSE, NULL);
		$this->registerArgument('inherit', 'integer', 'If 0 (zero), prevents inheritance of the value for this particular field - if inheritance is enabled by the ConfigurationProvider', FALSE, 99);
		$this->registerArgument('inheritEmpty', 'boolean', 'If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider', FALSE, TRUE);
		$this->registerArgument('clear', 'boolean', 'If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value', FALSE, FALSE);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
	}

	/**
	 * @param string $type
	 * @return FieldInterface
	 */
	protected function getPreparedComponent($type) {
		$component = $this->getForm()->createField($type, $this->arguments['name'], $this->arguments['label']);
		$component->setDefault($this->arguments['default']);
		$component->setRequired($this->arguments['required']);
		$component->setExclude($this->arguments['exclude']);
		$component->setEnable($this->arguments['enabled']);
		$component->setRequestUpdate($this->arguments['requestUpdate']);
		$component->setDisplayCondition($this->arguments['displayCond']);
		$component->setInherit($this->arguments['inherit']);
		$component->setInheritEmpty($this->arguments['inheritEmpty']);
		$component->setStopInheritance($this->arguments['stopInheritance']);
		$component->setTransform($this->arguments['transform']);
		$component->setClearable($this->arguments['clear']);
		$component->setVariables($this->arguments['variables']);
		return $component;
	}

}
