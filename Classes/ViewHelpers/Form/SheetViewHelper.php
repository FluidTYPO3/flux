<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;
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

use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * FlexForm sheet ViewHelper
 *
 * Groups FlexForm fields into sheets.
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class SheetViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the group, used as FlexForm sheet name, must be FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for the field group - used as tab name in FlexForm. Optional - if not ' .
			'specified, Flux tries to detect an LLL label named "flux.sheets.fluxFormId.foobar" based on sheet name, in ' .
			'scope of extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$form = $this->getForm();
		if (TRUE === $form->has($this->arguments['name'])) {
			$sheet = $form->get($this->arguments['name']);
			// Note: this next line will -override- any variables set in any existing sheet of that name. This
			// is expected behavior but it also affects previously added sheets.
			$sheet->setVariables($this->arguments['variables']);
			$this->setContainer($sheet);
		} else {
			/** @var Sheet $sheet */
			$sheet = $this->objectManager->get('FluidTYPO3\Flux\Form\Container\Sheet');
			$sheet->setName($this->arguments['name']);
			$sheet->setLabel($this->arguments['label']);
			$sheet->setVariables($this->arguments['variables']);
			$form->add($sheet);
			$this->setContainer($sheet);
		}
		$this->renderChildren();
	}

}
