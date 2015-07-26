<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;

/**
 * @package Flux
 */
class SectionTest extends AbstractContainerTest {

	/**
	 * @test
	 */
	public function canCreateFromDefinitionWithObjects() {
		$definition = [
			'name' => 'test',
			'label' => 'Test section',
			'objects' => [
				'object1' => [
					'label' => 'Test object',
					'fields' => [
						'foo' => [
							'type' => 'Input',
							'label' => 'Foo input'
						]
					]
				]
			]
		];
		$section = Section::create($definition);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Section', $section);
	}

}
