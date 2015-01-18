<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\RelationFieldInterface;

/**
 * Multi-table-relation FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class MultiRelationViewHelper extends AbstractRelationFieldViewHelper {

	/**
	 * @param string $type
	 * @return RelationFieldInterface
	 */
	public function getComponent($type = 'MultiRelation') {
		$component = $this->getPreparedComponent($type);
		return $component;
	}

}
