<?php
namespace FluidTYPO3\Flux\ViewHelpers\Grid;
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

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Flexform Grid Column ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Grid
 */
class ColumnViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Optional column name', FALSE, 'column');
		$this->registerArgument('label', 'string', 'Optional column label', FALSE, NULL);
		$this->registerArgument('colPos', 'integer', 'Optional column position. If you do not specify this it will be automatically assigned - so specify it if your template is dynamic and the output relies on this, as page rendering does for example!', FALSE, -1);
		$this->registerArgument('colspan', 'integer', 'Column span');
		$this->registerArgument('rowspan', 'integer', 'Row span');
		$this->registerArgument('style', 'string', 'Inline style to add when rendering the column');
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template. ' .
			'Can also be set and/or overridden in tag content using <flux:form.variable />', FALSE, array());
	}

	/**
	 * @return string
	 */
	public function render() {
		/** @var Column $column */
		$column = $this->getForm()->createContainer('Column', $this->arguments['name'], $this->arguments['label']);
		$column->setColspan($this->arguments['colspan']);
		$column->setRowspan($this->arguments['rowspan']);
		$column->setStyle($this->arguments['style']);
		$column->setColumnPosition($this->arguments['colPos']);
		$column->setVariables($this->arguments['variables']);
		$container = $this->getContainer();
		$container->add($column);
		$this->setContainer($column);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
