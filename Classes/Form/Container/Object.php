<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * @package Flux
 * @subpackage Form\Container
 */
class Tx_Flux_Form_Container_Object extends Tx_Flux_Form_Container_Container implements Tx_Flux_Form_ContainerInterface, Tx_Flux_Form_FieldContainerInterface {

	/**
	 * @return array
	 */
	public function build() {
		$label = $this->getLabel();
		$structureArray = array(
			'title' => $label, // read only by >4.7 and required in order to prevent the tx_templavoila from generating a deprecation warning
			'tx_templavoila' => array( // TODO: remove this when <4.7 no longer needs to be supported.
				'title' => $label
			),
			'type' => 'array',
			'el' => $this->buildChildren()
		);
		return $structureArray;
	}

	/**
	 * @return Tx_Flux_Form_FieldInterface[]
	 */
	public function getFields() {
		return (array) iterator_to_array($this->children);
	}

}
