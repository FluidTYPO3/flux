<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Grid container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class GridViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Optional name of this grid - defaults to "grid"', FALSE, 'grid');
		$this->registerArgument('label', 'string', 'Optional label for this grid - defaults to an LLL value (reported if it is missing)', FALSE, NULL);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$grid = $this->getGrid($this->arguments['name']);
		$grid->setExtensionName($this->getExtensionName());
		$grid->setParent($this->getForm());
		$grid->setLabel($this->arguments['label']);
		$grid->setVariables($this->arguments['variables']);
		$container = $this->getContainer();
		$this->setContainer($grid);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
