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

}
