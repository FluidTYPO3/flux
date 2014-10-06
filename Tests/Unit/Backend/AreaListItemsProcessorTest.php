<?php
namespace FluidTYPO3\Flux\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 ***************************************************************/

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class AreaListItemsProcessorTest
 */
class AreaListItemsProcessorTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function constructorSetsInternalAttributes() {
		$instance = new AreaListItemsProcessor();
		$this->assertAttributeInstanceOf('TYPO3\\CMS\\Extbase\\Object\\ObjectManager', 'objectManager', $instance);
		$this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\FluxService', 'fluxService', $instance);
		$this->assertAttributeInstanceOf('FluidTYPO3\\Flux\\Service\\RecordService', 'recordService', $instance);
	}

	/**
	 * @test
	 * @dataProvider getItemsProcFuncTestValues
	 * @param array $parameters
	 * @param string $urlParent
	 * @param string $urlArea
	 * @param array $expectedReturnedItems
	 * @param array $expectedItems
	 */
	public function testItemsProcFunc($parameters, $urlParent, $urlArea, $expectedReturnedItems = NULL, $expectedItems) {
		$mock = $this->getMock(
			$this->createInstanceClassName(),
			array('readParentAndAreaNameFromUrl', 'getContentAreasDefinedInContentElement')
		);
		$mock->expects($this->once())->method('readParentAndAreaNameFromUrl')
			->will($this->returnValue(array($urlParent, $urlArea)));
		if (NULL !== $expectedReturnedItems) {
			$mock->expects($this->once())->method('getContentAreasDefinedInContentElement')
				->will($this->returnValue($expectedReturnedItems));
		} else {
			$mock->expects($this->never())->method('getContentAreasDefinedInContentElement');
		}
		$mock->itemsProcFunc($parameters);
		$this->assertEquals($expectedItems, $parameters['items']);
	}

	/**
	 * @return array
	 */
	public function getItemsProcFuncTestValues() {
		return array(
			array(array(), NULL, NULL, NULL, array(array('', ''))),
			array(array(), 1, 'areaname', array(), array()),
			array(array(), 1, 'areaname', array(array('foobar', 'areaname')), array(1 => array('foobar', 'areaname'))),
		);
	}

	/**
	 * @test
	 */
	public function readParentAndAreaNameFromUrlReturnsArray() {
		$mock = new AreaListItemsProcessor();
		$result = $this->callInaccessibleMethod($mock, 'readParentAndAreaNameFromUrl');
		$this->assertCount(2, $result);
	}

	/**
	 * @test
	 */
	public function getContentAreasDefinedInContentElementCallsExpectedMethods() {
		$mock = new AreaListItemsProcessor();
		$provider1 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getGrid'));
		$provider1->expects($this->once())->method('getGrid')->will($this->returnValue(NULL));
		$provider2 = $this->objectManager->get('FluidTYPO3\\Flux\\Provider\\Provider');
		$providers = array($provider1, $provider2);
		$grid = $this->objectManager->get('FluidTYPO3\\Flux\\Form\\Container\\Grid');
		$row = $grid->createContainer('Row', 'row');
		$row->createContainer('Column', 'column1', 'Column 1');
		$row->createContainer('Column', 'column2', 'Column 2');
		$provider2->setGrid($grid);
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\RecordService', array('getSingle'));
		$recordService->expects($this->once())->method('getSingle')->will($this->returnValue(array('foo' => 'bar')));
		$fluxService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('resolveConfigurationProviders'));
		$fluxService->expects($this->once())->method('resolveConfigurationProviders')->will($this->returnValue($providers));
		ObjectAccess::setProperty($mock, 'fluxService', $fluxService, TRUE);
		ObjectAccess::setProperty($mock, 'recordService', $recordService, TRUE);
		$mock->getContentAreasDefinedInContentElement(0);

	}

}
