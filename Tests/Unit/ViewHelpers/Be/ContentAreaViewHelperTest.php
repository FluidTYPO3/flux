<?php
namespace FluidTYPO3\Flux\ViewHelpers\Be;
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
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Versioning\VersionState;

/**
 * @package Flux
 */
class ContentAreaViewHelperTest extends AbstractViewHelperTestCase {

	/**
	 * @test
	 * @dataProvider getDeleteMovePlaceholderTestValues
	 * @param $record
	 * @param $expectedResult
	 */
	public function testIsDeleteOrMovePlaceholder($record, $expectedResult) {
		$instance = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($instance, 'isDeleteOrMovePlaceholder', $record);
		$this->assertEquals($expectedResult, $result);
	}

	/**
	 * @return array
	 */
	public function getDeleteMovePlaceholderTestValues() {
		return array(
			array(array(), TRUE),
			array(array('uid' => 123), FALSE),
			array(array('t3ver_state' => VersionState::DELETE_PLACEHOLDER), TRUE)
		);
	}

	/**
	 * @test
	 * @dataProvider getRecordOverlayTestValues
	 * @param array $rows
	 * @param boolean $isDeletePlaceholder
	 */
	public function testProcessRecordOverlays(array $rows, $isDeletePlaceholder) {
		$mock = $this->getMock($this->createInstanceClassName(),
			array('getWorkspaceVersionOfRecordOrRecordItself', 'isDeleteOrMovePlaceholder'));
		$view = $this->getMock('TYPO3\\CMS\\Backend\\View\\PageLayoutView', array('isDisabled'));
		if (TRUE === $isDeletePlaceholder) {
			$mock->expects($this->exactly(2))->method('isDeleteOrMovePlaceholder')->will($this->returnValue(TRUE));
			$view->expects($this->never())->method('isDisabled');
		} else {
			$mock->expects($this->exactly(2))->method('isDeleteOrMovePlaceholder')->will($this->returnValue(FALSE));
			$view->expects($this->exactly(2))->method('isDisabled')->will($this->returnValue(TRUE));
		}
		$this->callInaccessibleMethod($mock, 'processRecordOverlays', $rows, $view);
	}

	/**
	 * @return array
	 */
	public function getRecordOverlayTestValues() {
		return array(
			array(array(array('foo'), array('bar')), TRUE),
			array(array(array('foo'), array('bar')), FALSE)
		);
	}

	/**
	 * @test
	 */
	public function configurePageLayoutViewForLanguageModeSetsLanguageVariablesInLanguageView() {
		$mock = $this->getMock($this->createInstanceClassName(), array('getPageModuleSettings'));
		$mock->expects($this->once())->method('getPageModuleSettings')->will($this->returnValue(array('function' => 2)));
		$view = new PageLayoutView();
		$clone = clone $view;
		$result = $this->callInaccessibleMethod($mock, 'configurePageLayoutViewForLanguageMode', $clone);
		$this->assertNotEquals($view, $result);
	}

	/**
	 * @test
	 */
	public function testGetWorkspaceVersionOfRecordOrRecordItself() {
		$mock = $this->getMock($this->createInstanceClassName(), array('getActiveWorkspaceId'));
		$mock->expects($this->once())->method('getActiveWorkspaceId')->will($this->returnValue(123));
		$record = array();
		$this->callInaccessibleMethod($mock, 'getWorkspaceVersionOfRecordOrRecordItself', $record);
	}

	/**
	 * @test
	 */
	public function testGetActiveWorkspaceId() {
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'getActiveWorkspaceId');
		$this->assertIsInteger($result);
	}

}
