<?php
namespace FluidTYPO3\Flux\Form\Field\Inline;
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
 * ************************************************************* */

use FluidTYPO3\Flux\Form\Field\AbstractFieldTest;
use FluidTYPO3\Flux\Form;

/**
 * @package Flux
 */
class FalTest extends AbstractFieldTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'collapseAll' => FALSE,
		'expandSingle' => FALSE,
		'newRecordLinkAddTitle' => FALSE,
		'newRecordLinkPosition' => Form::POSITION_TOP,
		'useCombination' => FALSE,
		'useSortable' => FALSE,
		'showPossibleLocalizationRecords' => FALSE,
		'showRemovedLocalizationRecords' => FALSE,
		'showAllLocalizationLink' => FALSE,
		'showSynchronizationLink' => FALSE,
		'enabledControls' => array(
			Form::CONTROL_INFO => FALSE,
			Form::CONTROL_NEW => TRUE,
			Form::CONTROL_DRAGDROP => TRUE,
			Form::CONTROL_SORT => TRUE,
			Form::CONTROL_HIDE => TRUE,
			Form::CONTROL_DELETE => FALSE,
			Form::CONTROL_LOCALISE => FALSE,
		)
	);

}
