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
 * @subpackage Form
 */
interface Tx_Flux_Form_RelationFieldInterface extends Tx_Flux_Form_MultiValueFieldInterface {

	/**
	 * @param string $condition
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setCondition($condition);

	/**
	 * @return string
	 */
	public function getCondition();

	/**
	 * @param string $foreignField
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignField($foreignField);

	/**
	 * @return string
	 */
	public function getForeignField();

	/**
	 * @param NULL|string $manyToMany
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setManyToMany($manyToMany);

	/**
	 * @return NULL|string
	 */
	public function getManyToMany();

	/**
	 * @param string $table
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setTable($table);

	/**
	 * @return string
	 */
	public function getTable();

	/**
	 * @param boolean $disableMovingChildrenWithParent
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setDisableMovingChildrenWithParent($disableMovingChildrenWithParent);

	/**
	 * @return boolean
	 */
	public function getDisableMovingChildrenWithParent();

	/**
	 * @param string $foreignDefaultSortby
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignDefaultSortby($foreignDefaultSortby);

	/**
	 * @return string
	 */
	public function getForeignDefaultSortby();

	/**
	 * @param string $foreignLabel
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignLabel($foreignLabel);

	/**
	 * @return string
	 */
	public function getForeignLabel();

	/**
	 * @param string $foreignSelector
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignSelector($foreignSelector);

	/**
	 * @return string
	 */
	public function getForeignSelector();

	/**
	 * @param string $foreignSortby
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignSortby($foreignSortby);

	/**
	 * @return string
	 */
	public function getForeignSortby();

	/**
	 * @param string $foreignTableField
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignTableField($foreignTableField);

	/**
	 * @return string
	 */
	public function getForeignTableField();

	/**
	 * @param string $foreignUnique
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setForeignUnique($foreignUnique);

	/**
	 * @return string
	 */
	public function getForeignUnique();

	/**
	 * @param string $itemListStyle
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setItemListStyle($itemListStyle);

	/**
	 * @return string
	 */
	public function getItemListStyle();

	/**
	 * @param string $localizationMode
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setLocalizationMode($localizationMode);

	/**
	 * @return string
	 */
	public function getLocalizationMode();

	/**
	 * @param boolean $localizeChildrenAtParentLocalization
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setLocalizeChildrenAtParentLocalization($localizeChildrenAtParentLocalization);

	/**
	 * @return boolean
	 */
	public function getLocalizeChildrenAtParentLocalization();

	/**
	 * @param string $selectedListStyle
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setSelectedListStyle($selectedListStyle);

	/**
	 * @return string
	 */
	public function getSelectedListStyle();

	/**
	 * @param string $symmetricField
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setSymmetricField($symmetricField);

	/**
	 * @return string
	 */
	public function getSymmetricField();

	/**
	 * @param string $symmetricLabel
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setSymmetricLabel($symmetricLabel);

	/**
	 * @return string
	 */
	public function getSymmetricLabel();

	/**
	 * @param string $symmetricSortby
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setSymmetricSortby($symmetricSortby);

	/**
	 * @return string
	 */
	public function getSymmetricSortby();

	/**
	 * @param boolean $showThumbnails
	 * @return Tx_Flux_Form_RelationFieldInterface
	 */
	public function setShowThumbnails($showThumbnails);

	/**
	 * @return boolean
	 */
	public function getShowThumbnails();

}
