<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\LegacyPreviewView;

/**
 * @package Flux
 */
class LegacyPreviewViewTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function testParseGridColumnTemplate() {
		$column = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Column', array('getColspan', 'getRowspan', 'getStyle', 'getLabel'));
		$column->expects($this->once())->method('getColSpan')->willReturn('foobar-colSpan');
		$column->expects($this->once())->method('getRowSpan')->willReturn('foobar-rowSpan');
		$column->expects($this->once())->method('getStyle')->willReturn('foobar-style');
		$column->expects($this->once())->method('getLabel')->willReturn('foobar-label');
		$subject = $this->getMock('FluidTYPO3\\Flux\\View\\LegacyPreviewView', array('drawNewIcon', 'drawPasteIcon'));
		$subject->expects($this->once())->method('drawNewIcon');
		$subject->expects($this->exactly(2))->method('drawPasteIcon');
		$this->callInaccessibleMethod($subject, 'parseGridColumnTemplate', array(), $column, 1, NULL, 'f-target', 2, 'f-content');
	}

}
