<?php
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Row extends AbstractFormContainer implements ContainerInterface {

	/**
	 * @return array
	 */
	public function build() {
		$structure = [
			'name' => $this->getName(),
			'label' => $this->getLabel(),
			'columns' => $this->buildChildren($this->children)
		];
		return $structure;
	}

	/**
	 * @return Column[]
	 */
	public function getColumns() {
		return (array) iterator_to_array($this->children);
	}

}
