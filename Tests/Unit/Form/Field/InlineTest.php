<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * InlineTest
 */
class InlineTest extends AbstractFieldTest {

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
		),
		'foreignTypes' => array(
			0 => array(
				'showitem' => 'a,b,c'
			)
		)
	);

}
