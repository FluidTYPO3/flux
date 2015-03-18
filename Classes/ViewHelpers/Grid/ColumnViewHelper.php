<?php
namespace FluidTYPO3\Flux\ViewHelpers\Grid;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * @return string
	 */
	public function render() {
		/** @var Column $column */
		$column = $this->getForm()->createContainer('Column', $this->arguments['name'], $this->arguments['label']);
		$column->setExtensionName($this->getExtensionName());
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
