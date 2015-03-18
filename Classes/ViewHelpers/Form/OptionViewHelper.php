<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
