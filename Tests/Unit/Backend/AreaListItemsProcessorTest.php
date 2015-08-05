<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\AreaListItemsProcessor;
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
			['readParentAndAreaNameFromUrl', 'getContentAreasDefinedInContentElement'],
			[], '', FALSE
		);
		$mock->expects($this->once())->method('readParentAndAreaNameFromUrl')
			->will($this->returnValue([$urlParent, $urlArea]));
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
		return [
			[[], NULL, NULL, NULL, [['', '']]],
			[[], 1, 'areaname', [], []],
			[[], 1, 'areaname', [['foobar', 'areaname']], [1 => ['foobar', 'areaname']]],
		];
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
		$grid = $this->getMock('FluidTYPO3\\Flux\\Form\\Container\\Grid', ['build']);
		$grid->expects($this->once())->method('build')->willReturn(['rows' => [['columns' => [[
			'label' => 'column-label',
			'name' => 'column'
		]]]]]);
		$mock = $this->getMock('FluidTYPO3\\Flux\\Backend\\AreaListItemsProcessor', ['dummy'], [], '', FALSE);
		$provider1 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', ['getGrid']);
		$provider1->expects($this->once())->method('getGrid')->willReturn(NULL);
		$provider2 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', ['getGrid']);
		$provider2->expects($this->once())->method('getGrid')->willReturn($grid);

		$providers = [$provider1, $provider2];
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\RecordService', ['getSingle']);
		$recordService->expects($this->once())->method('getSingle')->will($this->returnValue(['foo' => 'bar']));
		$fluxService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['resolveConfigurationProviders']);
		$fluxService->expects($this->once())->method('resolveConfigurationProviders')->willReturn($providers);
		ObjectAccess::setProperty($mock, 'fluxService', $fluxService, TRUE);
		ObjectAccess::setProperty($mock, 'recordService', $recordService, TRUE);
		$mock->getContentAreasDefinedInContentElement(1);

	}

}
