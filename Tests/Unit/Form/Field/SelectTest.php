<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\Form\Field\AbstractFieldTest;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * @package Flux
 */
class SelectTest extends AbstractFieldTest {

	/**
	 * @var array
	 */
	protected $chainProperties = [
		'name' => 'test',
		'label' => 'Test field',
		'itemListStyle' => 'color: red',
		'selectedListStyle' => 'color: blue',
		'emptyOption' => TRUE,
		'minItems' => 1,
		'maxItems' => 3,
		'requestUpdate' => TRUE,
	];

	/**
	 * @test
	 */
	public function canConsumeCommaSeparatedItems() {
		/** @var Select $instance */
		$instance = $this->createInstance();
		$instance->setItems('1,2');
		$this->assertSame(2, count($instance->getItems()));
	}

	/**
	 * @test
	 */
	public function canConsumeSingleDimensionalArrayItems() {
		/** @var Select $instance */
		$instance = $this->createInstance();
		$instance->setItems([1, 2]);
		$this->assertSame(2, count($instance->getItems()));
	}

	/**
	 * @test
	 */
	public function canConsumeMultiDimensionalArrayItems() {
		/** @var Select $instance */
		$instance = $this->createInstance();
		$items = [
			['foo' => 'bar'],
			['baz' => 'bay']
		];
		$instance->setItems($items);
		$this->assertSame(2, count($instance->getItems()));
	}

	/**
	 * @test
	 */
	public function canConsumeQueryObjectItems() {
		$GLOBALS['TCA']['foobar']['ctrl']['label'] = 'username';
		/** @var Select $instance */
		$instance = $this->objectManager->get($this->createInstanceClassName());
		$query = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query', ['execute', 'getType'], [], '', FALSE);
		$query->expects($this->any())->method('getType')->will($this->returnValue('foobar'));
		$query->expects($this->any())->method('execute')->will($this->returnValue([
			new FrontendUser('user1'),
			new FrontendUser('user2')
		]));
		$instance->setItems($query);
		$result = $instance->getItems();
		$this->assertIsArray($result);
		$this->assertEquals([
			['user1', NULL], ['user2', NULL]
		], $result);
	}

	/**
	 * @test
	 */
	public function getLabelPropertyNameTranslatesTableNameFromObjectTypeRespectingTableMapping() {
		$table = 'foo';
		$type = 'bar';
		$fixture = ['config' => ['tx_extbase' => ['persistence' => ['classes' =>
			[$type => ['mapping' => ['tableName' => $table . 'suffix']]]]]]];
		$service = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', ['getAllTypoScript']);
		$service->expects($this->once())->method('getAllTypoScript')->willReturn($fixture);
		$instance = $this->getMock($this->createInstanceClassName(), ['getConfigurationService']);
		$instance->expects($this->once())->method('getConfigurationService')->willReturn($service);
		$GLOBALS['TCA'][$table . 'suffix']['ctrl']['label'] = $table . 'label';
		$propertyName = $this->callInaccessibleMethod($instance, 'getLabelPropertyName', $table, $type);
		$this->assertEquals($table . 'label', $propertyName);
	}

}
