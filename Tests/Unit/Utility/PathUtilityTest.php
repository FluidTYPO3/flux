<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\PathUtility;

/**
 * @package Flux
 */
class PathUtilityTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function testGetTemplatePathFields() {
		$viewConfiguration = array(
			'templateRootPath' => 'EXT:foo/Resources/Private/Templates/',
			'templateRootPaths' => array(
				0 => 'EXT:foo/Resources/Private/Templates/'
			),
			'partialRootPaths' => array(
				0 => 'EXT:foo/Resources/Private/Partials/'
			),
			'layoutRootPaths' => array(
				0 => 'EXT:foo/Resources/Private/Layouts/'
			)
		);
		$mixedViewConfiguration = $viewConfiguration + array('enabled' => TRUE);
		$this->assertEquals($viewConfiguration, PathUtility::getTemplatePathFields($mixedViewConfiguration));
	}

}
