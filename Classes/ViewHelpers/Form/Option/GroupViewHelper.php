<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form\Option;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\Form\OptionViewHelper;

/**
 * Form group option ViewHelper
 */
class GroupViewHelper extends OptionViewHelper {

	/**
	 * @var string
	 */
	public static $option = Form::OPTION_GROUP;

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('value', 'string', 'Name of the group (fx: shown as label of WizardTab)', FALSE, NULL);
	}
}
