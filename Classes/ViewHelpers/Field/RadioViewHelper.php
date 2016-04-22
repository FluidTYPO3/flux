<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Radio;

/**
 * Radio FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class RadioViewHelper extends SelectViewHelper {

	/**
	 * @return Checkbox
	 */
	public static function getComponent() {
		/** @var Radio $component */
		$component = $this->getPreparedComponent('Radio');
		$component->setItems($this->arguments['items']);
		return $component;
	}

}
