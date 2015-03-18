<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
			'specified, Flux tries to detect an LLL label named "flux.fluxFormId.sheets.foobar" based on sheet name, in ' .
			'scope of extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
		$this->registerArgument('description', 'string', 'Optional string or LLL reference with a desription of the purpose of the sheet', FALSE, NULL);
		$this->registerArgument('shortDescription', 'string', 'Optional shorter version of description of purpose of the sheet, LLL reference supported', FALSE, NULL);
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
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
			$sheet->setExtensionName($this->getExtensionName());
			$sheet->setVariables($this->arguments['variables']);
			$this->setContainer($sheet);
		} else {
			/** @var Sheet $sheet */
			$sheet = $this->getForm()->createContainer('Sheet', $this->arguments['name'], $this->arguments['label']);
			$sheet->setExtensionName($this->getExtensionName());
			$sheet->setVariables($this->arguments['variables']);
			$sheet->setDescription($this->arguments['description']);
			$sheet->setShortDescription($this->arguments['shortDescription']);
			$form->add($sheet);
			$this->setContainer($sheet);
		}
		$this->renderChildren();
	}

}
