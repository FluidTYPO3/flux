<?php
namespace FluidTYPO3\Flux\Hooks;
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

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;

/**
 * @package Flux
 */
class WizardItemsHookSubscriberTest extends AbstractTestCase {

	/**
	 * @dataProvider getTestElementsWhiteAndBlackListsAndExpectedList
	 * @test
	 * @param array $items
	 * @param string $whitelist
	 * @param string $blacklist
	 * @param array $expectedList
	 */
	public function processesWizardItems($items, $whitelist, $blacklist, $expectedList) {
		$instance = $this->createInstance();
		$emulatedPageAndContentRecord = array('uid' => 1, 'tx_flux_column' => 'name');
		$controller = new NewContentElementController();
		$controller->colPos = 0;
		$controller->uid_pid = -1;
		$grid = new Grid();
		$row = new Row();
		$column = new Column();
		$column->setColumnPosition(0);
		$column->setName('name');
		$column->setVariable('allowedContentTypes', $whitelist);
		$column->setVariable('deniedContentTypes', $blacklist);
		$row->add($column);
		$grid->add($row);
		$provider1 = $this->objectManager->get('FluidTYPO3\\Flux\\Provider\\Provider');
		$provider1->setTemplatePaths(array());
		$provider1->setTemplateVariables(array());
		$provider1->setGrid($grid);
		$provider2 = $this->getMock('FluidTYPO3\\Flux\\Provider\\Provider', array('getGrid'));
		$provider2->expects($this->exactly(2))->method('getGrid')->will($this->returnValue(NULL));
		$configurationService = $this->getMock('FluidTYPO3\\Flux\\Service\\FluxService', array('resolveConfigurationProviders'));
		$configurationService->expects($this->exactly(2))->method('resolveConfigurationProviders')->will($this->returnValue(array($provider1, $provider2)));
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\RecordService', array('getSingle'));
		$recordService->expects($this->exactly(3))->method('getSingle')->will($this->returnValue($emulatedPageAndContentRecord));
		$instance->injectConfigurationService($configurationService);
		$instance->injectRecordService($recordService);
		$instance->manipulateWizardItems($items, $controller);
		$this->assertEquals($expectedList, $items);
	}

	/**
	 * @return array
	 */
	public function getTestElementsWhiteAndBlackListsAndExpectedList() {
		$items = array(
			'plugins' => array('title' => 'Nice header'),
			'plugins_test1' => array('tt_content_defValues' => array('CType' => 'test1')),
			'plugins_test2' => array('tt_content_defValues' => array('CType' => 'test2'))
		);
		return array(
			array(
				$items,
				NULL,
				NULL,
				array(
					'plugins' => array('title' => 'Nice header'),
					'plugins_test1' => array('tt_content_defValues' => array('CType' => 'test1')),
					'plugins_test2' => array('tt_content_defValues' => array('CType' => 'test2'))
				),
			),
			array(
				$items,
				'test1',
				NULL,
				array(
					'plugins' => array('title' => 'Nice header'),
					'plugins_test1' => array('tt_content_defValues' => array('CType' => 'test1'))
				),
			),
			array(
				$items,
				NULL,
				'test1',
				array(
					'plugins' => array('title' => 'Nice header'),
					'plugins_test2' => array('tt_content_defValues' => array('CType' => 'test2'))
				),
			),
			array(
				$items,
				'test1',
				'test1',
				array(),
			),
		);
	}

	/**
	 * @test
	 */
	public function applyDefaultValuesAppliesValues() {
		$instance = new WizardItemsHookSubscriber();
		$defaultValues = array('tx_flux_column' => 'foobararea', 'tx_flux_parent' => 321);
		$items = array(
			array('tt_content_defValues' => '', 'params' => '')
		);
		$result = $this->callInaccessibleMethod($instance, 'applyDefaultValues', $items, $defaultValues);
		$this->assertEquals($defaultValues['tx_flux_column'], $result[0]['tt_content_defValues']['tx_flux_column']);
		$this->assertEquals($defaultValues['tx_flux_parent'], $result[0]['tt_content_defValues']['tx_flux_parent']);
		$this->assertContains('[tx_flux_column]=foobararea', $result[0]['params']);
		$this->assertContains('[tx_flux_parent]=321', $result[0]['params']);
	}

	/**
	 * @test
	 */
	public function testManipulateWizardItemsWithDefaultValues() {
		$defaultValues = array('tx_flux_column' => 'foobararea', 'tx_flux_parent' => 321);
		$items = array(
			array('tt_content_defValues' => '', 'params' => '')
		);
		$instance = $this->getMock(
			$this->createInstanceClassName(),
			array(
				'getDefaultValues', 'readWhitelistAndBlacklistFromPageColumn', 'readWhitelistAndBlacklistFromColumn',
				'applyDefaultValues', 'applyWhitelist', 'applyBlacklist', 'trimItems'
			)
		);
		$lists = array(array(), array());
		$instance->expects($this->once())->method('readWhitelistAndBlacklistFromPageColumn')->will($this->returnValue($lists));
		$instance->expects($this->once())->method('readWhitelistAndBlacklistFromColumn')->will($this->returnValue($lists));
		$instance->expects($this->once())->method('applyDefaultValues')->will($this->returnValue($items));
		$instance->expects($this->once())->method('applyWhitelist')->will($this->returnValue($items));
		$instance->expects($this->once())->method('applyBlacklist')->will($this->returnValue($items));
		$instance->expects($this->once())->method('trimItems')->will($this->returnValue($items));
		$instance->expects($this->once())->method('getDefaultValues')->will($this->returnValue($defaultValues));
		$controller = new NewContentElementController();
		$instance->manipulateWizardItems($items, $controller);
		$this->assertNotEmpty($items);
	}

}
