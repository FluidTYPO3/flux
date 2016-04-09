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
 * Checkbox FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class RadioViewHelper extends AbstractMultiValueFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('items', 'mixed', 'Items for the radio; array / CSV / Traversable / Query supported', TRUE);
	}

	/**
	 * @return Checkbox
	 */
	public function getComponent() {
		/** @var Radio $component */
		$component = $this->getPreparedComponent('Radio');
		$component->setItems($this->arguments['items']);
		return $component;
	}

}
