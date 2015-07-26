<?php
namespace FluidTYPO3\Flux\Tests\Unit\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * @package Flux
 */
class ClearValueWizardTest extends AbstractUserFunctionTest {

	/**
	 * @var array
	 */
	protected $parameters = [
		'parameters' => [
			'itemName' => 'test[foo][bar]'
		]
	];

	/**
	 * @var string
	 */
	protected $methodName = 'renderField';

}
