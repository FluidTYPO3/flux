<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\MultiValueFieldInterface;

/**
 * Base class for all FlexForm fields.
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
abstract class AbstractMultiValueFieldViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('validate', 'string', 'FlexForm-type validation configuration for this input', FALSE, 'trim');
		$this->registerArgument('size', 'integer', 'Size of the selector box', FALSE, 1);
		$this->registerArgument('multiple', 'boolean', 'If TRUE, allows multiple selections', FALSE, FALSE);
		$this->registerArgument('minItems', 'integer', 'Minimum required number of items to be selected', FALSE, 0);
		$this->registerArgument('maxItems', 'integer', 'Maxium allowed number of items to be selected', FALSE, 1);
		$this->registerArgument('itemListStyle', 'string', 'Overrides the default list style when maxItems > 1', FALSE, NULL);
		$this->registerArgument('selectedListStyle', 'string', 'Overrides the default selected list style when maxItems > 1 and renderMode is default', FALSE, NULL);
		$this->registerArgument('renderMode', 'string', 'Alternative rendering mode - default is an HTML select field but you can also use fx "checkbox" - see TCA select field "renderMode" attribute', FALSE, 'default');
	}

	/**
	 * @param string $type
	 * @return MultiValueFieldInterface
	 */
	protected function getPreparedComponent($type) {
		/** @var MultiValueFieldInterface $component */
		$component = parent::getPreparedComponent($type);
		$component->setMinItems($this->arguments['minItems']);
		$component->setMaxItems($this->arguments['maxItems']);
		$component->setSize($this->arguments['size']);
		$component->setMultiple($this->arguments['multiple']);
		$component->setRenderMode($this->arguments['renderMode']);
		$component->setItemListStyle($this->arguments['itemListStyle']);
		$component->setSelectedListStyle($this->arguments['selectedListStyle']);
		return $component;
	}


}
