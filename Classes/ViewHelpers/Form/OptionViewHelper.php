<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Göran Bodenschatz <coding@46halbe.de>
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Form option ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class OptionViewHelper extends AbstractFormViewHelper {

	/**
	 * @var string
	 */
	protected $option;

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the option to be set', TRUE, NULL);
		$this->registerArgument('value', 'string', 'Option value', FALSE, NULL);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$option = $this->hasArgument('name') ? $this->arguments['name'] : $this->option;
		$value = NULL === $this->arguments['value'] ? $this->renderChildren() : $this->arguments['value'];

		$this->getForm()->setOption($option, $value);
	}
}
