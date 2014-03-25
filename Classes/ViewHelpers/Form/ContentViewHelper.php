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

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Adds a content area to a source using Flux FlexForms
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
			'Flux tries to detect an LLL label named "flux.fluxFormId.areas.foobar" based on area name, in scope of ' .
			'extension rendering the Flux form.', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return string
	 */
	public function render() {
		/** @var FluidTYPO3\Flux\Form\Container\Content $content */
		$content = $this->getForm()->createContainer('Content', $this->arguments['name'], $this->arguments['label']);
		$container = $this->getContainer();
		$container->add($content);
		$this->setContainer($content);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
