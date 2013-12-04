<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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
 * ************************************************************* */

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Form_Field_InlineTest extends Tx_Flux_Tests_Functional_Form_Field_AbstractFieldTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'collapseAll' => FALSE,
		'expandSingle' => FALSE,
		'newRecordLinkAddTitle' => FALSE,
		'newRecordLinkPosition' => Tx_Flux_Form::POSITION_TOP,
		'useCombination' => FALSE,
		'useSortable' => FALSE,
		'showPossibleLocalizationRecords' => FALSE,
		'showRemovedLocalizationRecords' => FALSE,
		'showAllLocalizationLink' => FALSE,
		'showSynchronizationLink' => FALSE,
		'enabledControls' => array(
			Tx_Flux_Form::CONTROL_INFO => FALSE,
			Tx_Flux_Form::CONTROL_NEW => TRUE,
			Tx_Flux_Form::CONTROL_DRAGDROP => TRUE,
			Tx_Flux_Form::CONTROL_SORT => TRUE,
			Tx_Flux_Form::CONTROL_HIDE => TRUE,
			Tx_Flux_Form::CONTROL_DELETE => FALSE,
			Tx_Flux_Form::CONTROL_LOCALISE => FALSE,
		)
	);

}
