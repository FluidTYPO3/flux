<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\UserFunction;

/**
 * Flexform Userfunc field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class UserFuncViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('userFunc', 'string', 'Classname->function notation of UserFunc to be called, example "Tx_Myext_Configuration_FlexForms_MyField->renderField" - Extbase classes need autoload registry for this', TRUE);
		$this->registerArgument('arguments', 'array', 'Optional array of arguments to pass to the UserFunction building this field');
	}

	/**
	 * Render method
	 * @param string $type
	 * @return UserFunction
	 */
	public function getComponent($type = 'UserFunction') {
		/** @var UserFunction $user */
		$user = $this->getPreparedComponent($type);
		$user->setFunction($this->arguments['userFunc']);
		$user->setArguments($this->arguments['arguments']);
		return $user;
	}

}
