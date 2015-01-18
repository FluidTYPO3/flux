<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Adds a content area to a source using Flux FlexForms
 *
 * Only works to insert a single content area into your element.
 * To insert multiple content areas, use instead a full `flux:grid`
 * with your desired row and column structure; each column then
 * becomes a content area.
 *
 * Using `flux:grid` after this ViewHelper in the same `flux:form`
 * will overwrite this ViewHelper.
 *
 * Using this ViewHelper after `flux:grid` will cause this ViewHelper
 * to be ignored.
 *
 * ### Example of difference
 *
 * ```xml
 * <flux:form id="myform">
 *     <!-- Creates a basic Grid with one row and one column, names
 *          the column "mycontent" and makes Flux use this Grid -->
 *     <flux:content name="mycontent" />
 *     <!-- Additional flux:content tags are completely ignored -->
 * </flux:form>
 * ```
 *
 * ```xml
 * <flux:form id="myform">
 *     <!-- Creates a full, multi-column/row Grid -->
 *     <flux:grid>
 *         <flux:grid.row>
 *             <flux:grid.column name="mycontentA" />
 *             <flux:grid.column name="mycontentB" />
 *         </flux:grid.row>
 *         <flux:grid.row>
 *             <flux:grid.column name="mycontentC" colspan="2" />
 *         </flux:grid.row>
 *     </flux:grid>
 *     <!-- No use of flux:content is possible after this point -->
 * </flux:form>
 * ```
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class ContentViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the content area, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for content area, can be LLL: value. Optional - if not specified, ' .
			'Flux tries to detect an LLL label named "flux.fluxFormId.columns.foobar" based on column name, in scope of ' .
			'extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		$originalContainer = $this->getContainer();
		if (FALSE === $originalContainer instanceof Column) {
			// get the current Grid and check for existence of one row and one column, if missing then create them:
			$grid = $this->getGrid('grid');
			if (0 === count($grid->getRows())) {
				$grid->setExtensionName($this->getExtensionName());
				$row = $grid->createContainer('Row', 'row');
				$column = $row->createContainer('Column', 'column');
				$column->setName($this->arguments['name']);
				$column->setLabel($this->arguments['label']);
			}
		}
	}

}
