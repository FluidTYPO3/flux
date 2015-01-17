<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;
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

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;

/**
 * @package Flux
 */
class ContentViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 */
	public function createsGridIfNotSet() {
		$column = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Column', array('setName', 'setLabel'));
		$column->expects($this->once())->method('setName');
		$column->expects($this->once())->method('setLabel');
		$row = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Row', array('createContainer'));
		$grid = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Grid', array('createContainer'));
		$grid->expects($this->once())->method('createContainer')->will($this->returnValue($row));
		$row->expects($this->once())->method('createContainer')->will($this->returnValue($column));
		$mock = $this->getMock($this->createInstanceClassName(), array('getContainer', 'getGrid'));
		$mock->expects($this->once())->method('getContainer')->will($this->returnValue(NULL));
		$mock->expects($this->once())->method('getGrid')->will($this->returnValue($grid));
		$mock->render();
	}

}
