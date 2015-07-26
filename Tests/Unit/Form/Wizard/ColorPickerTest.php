<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * @package Flux
 */
class ColorPickerTest extends AbstractWizardTest {

	/**
	 * @var array
	 */
	protected $chainProperties = [
		'name' => 'test',
		'label' => 'Test field',
		'hideParent' => FALSE,
		'dimensions' => '40x40',
		'width' => 100,
		'height' => 100
	];

}
