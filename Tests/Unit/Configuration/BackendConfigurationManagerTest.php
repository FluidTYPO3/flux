<?php
namespace FluidTYPO3\Flux\Configuration;
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

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package FluidTYPO3\Flux
 */
class BackendConfigurationManagerTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canCreateInstance() {
		$instance = $this->createInstance();
		$this->assertInstanceOf($this->createInstanceClassName(), $instance);
	}

	/**
	 * @test
	 */
	public function getPageIdFromContentObjectUsesGetFromRecordIfFilled() {
		$record = Records::$contentRecordWithParentAndWithoutChildren;
		$mockContentObject = new \stdClass();
		$mockContentObject->data = $record;
		$mock = $this->getMock($this->createInstanceClassName(), array('getPageIdFromRecord', 'getContentObject'));
		$mock->expects($this->at(0))->method('getContentObject')->will($this->returnValue($mockContentObject));
		$mock->expects($this->at(1))->method('getPageIdFromRecord')->with($record);
		$this->callInaccessibleMethod($mock, 'getPageIdFromContentObject');
	}

	/**
	 * @test
	 */
	public function getPageIdFromRecordReturnsPidProperty() {
		$record = Records::$contentRecordWithParentAndWithoutChildren;
		$record['pid'] = 123;
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'getPageIdFromRecord', $record);
		$this->assertEquals(123, $result);
	}

	/**
	 * @test
	 */
	public function getPageIdFromRecordReturnsZeroIfPropertyEmpty() {
		$record = Records::$contentRecordWithParentAndWithoutChildren;
		$record['pid'] = '';
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'getPageIdFromRecord', $record);
		$this->assertEquals(0, $result);
	}

	/**
	 * @test
	 */
	public function getPageIdFromGetReturnsExpectedValue() {
		$_GET['id'] = 123;
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'getPageIdFromGet');
		$this->assertEquals(123, $result);
		unset($_GET['id']);
	}

	/**
	 * @test
	 */
	public function getPageIdFromPostReturnsExpectedValue() {
		$_POST['id'] = 123;
		$mock = $this->getMock($this->createInstanceClassName());
		$result = $this->callInaccessibleMethod($mock, 'getPageIdFromPost');
		$this->assertEquals(123, $result);
		unset($_POST['id']);
	}

	/**
	 * @test
	 */
	public function getCurrentPageIdReturnsProtectedPropertyOnlyIfSet() {
		$pageUid = 54642;

		$mock = $this->objectManager->get($this->createInstanceClassName());
		ObjectAccess::setProperty($mock, 'currentPageUid', 0, TRUE);
		ObjectAccess::setProperty($mock, 'recordService', $this->objectManager->get('FluidTYPO3\Flux\Service\RecordService'), TRUE);
		$result = $this->callInaccessibleMethod($mock, 'getCurrentPageId');
		$this->assertNotEquals($pageUid, $result);
		ObjectAccess::setProperty($mock, 'currentPageUid', $pageUid, TRUE);
		$result = $this->callInaccessibleMethod($mock, 'getCurrentPageId');
		$this->assertEquals($pageUid, $result);
		ObjectAccess::setProperty($mock, 'currentPageUid', 0, TRUE);
	}

}
