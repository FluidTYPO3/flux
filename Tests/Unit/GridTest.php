<?php
namespace FluidTYPO3\Flux;
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

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * @package Flux
 */
class GridTest extends AbstractTestCase {

	/**
	 * @param string $gridName
	 * @param string $template
	 * @return Grid
	 */
	protected function getDummyGridFromTemplate($gridName = 'grid', $template = self::FIXTURE_TEMPLATE_BASICGRID) {
		$templatePathAndFilename = $this->getAbsoluteFixtureTemplatePathAndFilename($template);
		$service = $this->createFluxServiceInstance();
		$grid = $service->getGridFromTemplateFile($templatePathAndFilename, 'Configuration', $gridName, array(), 'flux');
		return $grid;
	}

	/**
	 * @test
	 */
	public function canRetrieveStoredGrid() {
		$grid = $this->getDummyGridFromTemplate();
		$this->assertIsValidAndWorkingGridObject($grid);
	}

	/**
	 * @test
	 */
	public function canReturnGridObjectWithoutGridPresentInTemplate() {
		$grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_WITHOUTFORM);
		$this->assertIsValidAndWorkingGridObject($grid);
	}

	/**
	 * @test
	 */
	public function canReturnFallbackGridObjectWhenUsingIncorrectGridName() {
		$grid = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_BASICGRID);
		$this->assertIsValidAndWorkingGridObject($grid);
	}

	/**
	 * @test
	 */
	public function canReturnGridObjectWithDualGridsPresentInTemplate() {
		$grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
		$grid2 = $this->getDummyGridFromTemplate('grid2', self::FIXTURE_TEMPLATE_DUALGRID);
		$this->assertIsValidAndWorkingGridObject($grid1);
		$this->assertIsValidAndWorkingGridObject($grid2);
	}

	/**
	 * @test
	 */
	public function canReturnGridObjectOneFallbackWithDualGridsPresentInTemplate() {
		$grid1 = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_DUALGRID);
		$grid2 = $this->getDummyGridFromTemplate('doesnotexist', self::FIXTURE_TEMPLATE_DUALGRID);
		$this->assertIsValidAndWorkingGridObject($grid1);
		$this->assertIsValidAndWorkingGridObject($grid2);
	}

	/**
	 * @test
	 */
	public function canReturnOneGridWithTwoRowsFromTemplateWithDualGridsWithSameNameAndOneRowEach() {
		$grid = $this->getDummyGridFromTemplate('grid', self::FIXTURE_TEMPLATE_COLLIDINGGRID);
		$this->assertIsValidAndWorkingGridObject($grid);
		$this->assertSame(2, count($grid->getRows()));
	}

}
