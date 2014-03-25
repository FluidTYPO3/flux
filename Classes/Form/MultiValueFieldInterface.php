<?php
namespace FluidTYPO3\Flux\Form;
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

/**
 * @package Flux
 * @subpackage Form
 */
interface MultiValueFieldInterface extends FieldInterface {

	/**
	 * @param integer $size
	 * @return MultiValueFieldInterface
	 */
	public function setSize($size);

	/**
	 * @return integer
	 */
	public function getSize();

	/**
	 * @param boolean $multiple
	 */
	public function setMultiple($multiple);

	/**
	 * @return boolean
	 */
	public function getMultiple();

	/**
	 * @param integer $maxItems
	 * @return MultiValueFieldInterface
	 */
	public function setMaxItems($maxItems);

	/**
	 * @return integer
	 */
	public function getMaxItems();

	/**
	 * @param integer $minItems
	 * @return MultiValueFieldInterface
	 */
	public function setMinItems($minItems);

	/**
	 * @return integer
	 */
	public function getMinItems();

	/**
	 * @param string $itemListStyle
	 * @return MultiValueFieldInterface
	 */
	public function setItemListStyle($itemListStyle);

	/**
	 * @return string
	 */
	public function getItemListStyle();

	/**
	 * @param string $selectedListStyle
	 * @return MultiValueFieldInterface
	 */
	public function setSelectedListStyle($selectedListStyle);

	/**
	 * @return string
	 */
	public function getSelectedListStyle();

}
