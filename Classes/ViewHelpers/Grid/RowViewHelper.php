<?php
namespace FluidTYPO3\Flux\ViewHelpers\Grid;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Flexform Grid Row ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Grid
 */
class RowViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Optional name of this row - defaults to "row"', FALSE, 'row');
		$this->registerArgument('label', 'string', 'Optional label for this row - defaults to an LLL value (reported if it is missing)', FALSE, NULL);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$name = ('row' === $this->arguments['name'] ? uniqid('row', TRUE) : $this->arguments['name']);
		$row = $this->getForm()->createContainer('Row', $name, $this->arguments['label']);
		$row->setExtensionName($this->getExtensionName());
		$row->setVariables($this->arguments['variables']);
		$container = $this->getContainer();
		$container->add($row);
		$this->setContainer($row);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
