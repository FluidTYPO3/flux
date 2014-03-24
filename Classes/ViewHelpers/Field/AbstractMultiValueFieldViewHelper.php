<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
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
