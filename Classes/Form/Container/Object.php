<?php
namespace FluidTYPO3\Flux\Form\Container;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Object extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface {

	/**
	 * @return array
	 */
	public function build() {
		$label = $this->getLabel();
		$structureArray = array(
			'title' => $label,
			'type' => 'array',
			'el' => $this->buildChildren()
		);
		$structureArray['tx_templavoila'] = array('title' => $structureArray['title']); // patch: TYPO3 core legacy required for section objects.
		return $structureArray;
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFields() {
		return (array) iterator_to_array($this->children);
	}

}
