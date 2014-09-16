<?php
namespace FluidTYPO3\Flux\Service;
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

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * @package Flux
 */
class RecordServiceTest extends AbstractTestCase {

	/**
	 * @var DatabaseConnection
	 */
	private static $connectionMock;

	/**
	 * @param array $methods
	 * @return DatabaseConnection
	 */
	protected function getMockDatabaseConnection(array $methods) {
		self::$connectionMock = $this->getMock($this->createInstanceClassName(), $methods);
		return self::$connectionMock;
	}

	/**
	 * @param array $methods
	 * @param array $connectionMethods
	 * @return RecordService
	 */
	protected function getMockServiceInstance(array $methods, array $connectionMethods) {
		$methods[] = 'getDatabaseConnection';
		$mock = $this->getAccessibleMock($this->createInstanceClassName(), $methods);
		$connectionMock = $this->getMockDatabaseConnection($connectionMethods);
		$mock->expects($this->atLeastOnce())->method('getDatabaseConnection')->will($this->returnValue($connectionMock));
		return $mock;
	}

	/**
	 * @test
	 */
	public function getMethodCallsExpectedDatabaseMethod() {
		$table = 'test';
		$fields = 'a,b';
		$clause = '1=2';
		$groupBy = 'foo';
		$orderBy = 'bar';
		$limit = 60;
		$mock = $this->getMockServiceInstance(array(), array('exec_SELECTgetRows'));
		self::$connectionMock->expects($this->once())->method('exec_SELECTgetRows')->with($fields, $table, $clause, $groupBy, $orderBy, $limit);
		$mock->get($table, $fields, $clause, $groupBy, $orderBy, $limit);
	}

	/**
	 * @test
	 */
	public function getSingleMethodCallsExpectedDatabaseMethod() {
		$table = 'test';
		$fields = 'a,b';
		$uid = 123;
		$mock = $this->getMockServiceInstance(array(), array('exec_SELECTgetSingleRow'));
		self::$connectionMock->expects($this->once())->method('exec_SELECTgetSingleRow')->with($fields, $table, "uid = '" . $uid . "'");
		$mock->getSingle($table, $fields, $uid);
	}

	/**
	 * @test
	 */
	public function updateMethodCallsExpectedDatabaseMethod() {
		$table = 'test';
		$uid = 123;
		$fields = array('foo' => 'bar', 'uid' => $uid);
		$mock = $this->getMockServiceInstance(array(), array('exec_UPDATEquery'));
		self::$connectionMock->expects($this->once())->method('exec_UPDATEquery')->with($table, "uid = '" . $uid . "'", $fields);
		$mock->update($table, $fields, $uid);
	}

	/**
	 * @test
	 */
	public function deleteMethodCallsExpectedDatabaseMethodWithUid() {
		$table = 'test';
		$uid = 123;
		$mock = $this->getMockServiceInstance(array(), array('exec_DELETEquery'));
		self::$connectionMock->expects($this->once())->method('exec_DELETEquery')->with($table, "uid = '" . $uid . "'");
		$mock->delete($table, $uid);
	}

	/**
	 * @test
	 */
	public function deleteMethodCallsExpectedDatabaseMethodWithRecord() {
		$table = 'test';
		$uid = 123;
		$record = array('uid' => 123);
		$mock = $this->getMockServiceInstance(array(), array('exec_DELETEquery'));
		self::$connectionMock->expects($this->once())->method('exec_DELETEquery')->with($table, "uid = '" . $uid . "'");
		$mock->delete($table, $record);
	}

	/**
	 * @test
	 */
	public function returnsDatabaseConnection() {
		$instance = $this->createInstance();
		$this->assertSame($GLOBALS['TYPO3_DB'], $this->callInaccessibleMethod($instance, 'getDatabaseConnection'));
	}

}
