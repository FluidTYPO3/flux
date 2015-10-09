<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Select;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Select-type FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class SelectViewHelper extends AbstractMultiValueFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('items', 'mixed', 'Items for the selector; array / CSV / Traversable / Query supported', TRUE);
		$this->registerArgument('emptyOption', 'mixed', 'If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property\'s value (cast to string) as label.', FALSE, FALSE);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return Select
	 */
	public static function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		/** @var Select $component */
		$component = static::getPreparedComponent('Select', $renderingContext, $arguments);
		$component->setItems($arguments['items']);
		$component->setEmptyOption($arguments['emptyOption']);
		return $component;
	}

}
