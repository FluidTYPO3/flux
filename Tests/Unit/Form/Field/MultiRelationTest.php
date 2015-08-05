<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * @package Flux
 */
class MultiRelationTest extends AbstractFieldTest {

	/**
	 * @var array
	 */
	protected $chainProperties = [
		'name' => 'test',
		'label' => 'Test field',
		'table' => 'pages',
		'foreignLabel' => 'uid',
		'filter' => [
			'test' => 'test'
		]
	];

}
