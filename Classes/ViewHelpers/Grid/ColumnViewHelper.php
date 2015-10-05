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
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

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
		$this->registerArgument('name', 'string', 'Identifies your column in generated configuration; also used as target ID when column is inside a container content element. Page-level content columns use "colPos" instead.', FALSE, 'column');
		$this->registerArgument('label', 'string', 'Optional column label', FALSE, NULL);
		$this->registerArgument('colPos', 'integer', 'Page column number; use only when creating root page content columns. Container elements use "name" instead.', FALSE, -1);
		$this->registerArgument('colspan', 'integer', 'Column span');
		$this->registerArgument('rowspan', 'integer', 'Row span');
		$this->registerArgument('style', 'string', 'Inline style to add when rendering the column');
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template. ' .
			'Can also be set and/or overridden in tag content using <flux:form.variable />', FALSE, array());
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return Column
	 */
	static public function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		/** @var Column $column */
		$column = static::getFormFromRenderingContext($renderingContext)->createContainer('Column', $arguments['name'], $arguments['label']);
		$column->setExtensionName(static::getExtensionNameFromRenderingContextOrArguments($renderingContext, $arguments));
		$column->setColspan($arguments['colspan']);
		$column->setRowspan($arguments['rowspan']);
		$column->setStyle($arguments['style']);
		$column->setColumnPosition($arguments['colPos']);
		$column->setVariables($arguments['variables']);
		return $column;
	}

}
