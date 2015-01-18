<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field\Inline;

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
class FalViewHelperTest extends AbstractFieldViewHelperTestCase {

	/**
	 * @test
	 */
	public function createsExpectedComponent() {
		$arguments = array(
			'name' => 'test'
		);
		$instance = $this->buildViewHelperInstance($arguments, array());
		$component = $instance->getComponent();
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Field\Inline\Fal', $component);
	}

	/**
	 * @test
	 */
	public function supportsHeaderThumbnail() {
		$arguments = array(
			'name' => 'test',
			'headerThumbnail' => array('test' => 'test')
		);
		$instance = $this->buildViewHelperInstance($arguments, array());
		$component = $instance->getComponent();
		$this->assertEquals($arguments['headerThumbnail'], $component->getHeaderThumbnail());
	}

	/**
	 * @test
	 */
	public function supportsForeignMatchFields() {
		$arguments = array(
			'name' => 'test',
			'foreignMatchFields' => array('test' => 'test')
		);
		$instance = $this->buildViewHelperInstance($arguments, array());
		$component = $instance->getComponent();
		$this->assertEquals($arguments['foreignMatchFields'], $component->getForeignMatchFields());
	}

}
