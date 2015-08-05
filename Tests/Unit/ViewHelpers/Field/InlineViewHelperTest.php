<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\AbstractFieldViewHelperTestCase;

/**
 * @package Flux
 */
class InlineViewHelperTest extends AbstractFieldViewHelperTestCase {

	/**
	 * @var array
	 */
	protected $defaultArguments = [
		'name' => 'test',
		'enabledControls' => [
			'new' => TRUE,
			'hide' => TRUE
		],
		'foreignTypes' => [
			0 => [
				'showitem' => 'a,b,c'
			]
		]
	];

}
