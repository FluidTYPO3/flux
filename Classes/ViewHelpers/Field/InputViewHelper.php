<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;

/**
 * Input FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class InputViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('eval', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('size', 'integer', 'Size of field', FALSE, 32);
		$this->registerArgument('maxCharacters', 'integer', 'Maximum number of characters allowed', FALSE);
		$this->registerArgument('minimum', 'integer', 'Minimum value for integer type fields', FALSE);
		$this->registerArgument('maximum', 'integer', 'Maximum value for integer type fields', FALSE);
		$this->registerArgument('placeholder', 'string', 'Placeholder text which vanishes if field is filled and/or field is focused');
	}

	/**
	 * @return Input
	 */
	public function getComponent() {
		/** @var Input $input */
		$input = $this->getPreparedComponent('Input');
		$input->setValidate($this->arguments['eval']);
		$input->setMaxCharacters($this->arguments['maxCharacters']);
		$input->setMinimum($this->arguments['minimum']);
		$input->setMaximum($this->arguments['maximum']);
		$input->setPlaceholder($this->arguments['placeholder']);
		$input->setSize($this->arguments['size']);
		return $input;
	}

}
