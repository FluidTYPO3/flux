<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\RecordService;
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
	public function supportsInjectors() {
		$instance = new BackendConfigurationManager();
		$recordService = new RecordService();
		$instance->injectRecordService($recordService);
		$this->assertAttributeSame($recordService, 'recordService', $instance);
	}

	/**
	 * @test
	 */
	public function canSetCurrentPageId() {
		$instance = new BackendConfigurationManager();
		$instance->setCurrentPageId(123);
		$this->assertAttributeEquals(123, 'currentPageUid', $instance);
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
		$mock = $this->getMock($this->createInstanceClassName(), array('getContentObject', 'getPrioritizedPageUids'));
		$mock->expects($this->once())->method('getPrioritizedPageUids')->willReturn(array(0, 0, 0, 0, 1));
		ObjectAccess::setProperty($mock, 'currentPageUid', 0, TRUE);
		$result = $this->callInaccessibleMethod($mock, 'getCurrentPageId');
		$this->assertNotEquals($pageUid, $result);
		ObjectAccess::setProperty($mock, 'currentPageUid', $pageUid, TRUE);
		$result = $this->callInaccessibleMethod($mock, 'getCurrentPageId');
		$this->assertEquals($pageUid, $result);
		ObjectAccess::setProperty($mock, 'currentPageUid', 0, TRUE);
	}

	/**
	 * @test
	 */
	public function getPageIdFromRecordUidDelegatesToRecordService() {
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\RecordService', array('getSingle'));
		$recordService->expects($this->once())->method('getSingle')
			->with('table', 'pid', 123)->will($this->returnValue(array('foo' => 'bar')));
		$mock = $this->objectManager->get($this->createInstanceClassName());
		$mock->injectRecordService($recordService);
		$this->callInaccessibleMethod($mock, 'getPageIdFromRecordUid', 'table', 123);
	}

}
