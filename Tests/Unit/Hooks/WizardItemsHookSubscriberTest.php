<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;

/**
 * WizardItemsHookSubscriberTest
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
		$instance = $this->getMock(
			'FluidTYPO3\\Flux\\Hooks\\WizardItemsHookSubscriber',
			array('getAreaNameAndParentFromRelativeRecordOrDefaults')
		);
		$instance->expects($this->once())->method('getAreaNameAndParentFromRelativeRecordOrDefaults')
			->willReturn(array(1, 'area'));
		$emulatedPageAndContentRecord = array('uid' => 1, 'tx_flux_column' => 'area');
		$controller = $this->getMock(NewContentElementController::class, array('init'), array(), '', FALSE);
		$controller->colPos = 0;
		$controller->uid_pid = -1;
		$grid = new Grid();
		$row = new Row();
		$column = new Column();
		$column->setColumnPosition(0);
		$column->setName('area');
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
		$configurationService->expects($this->exactly(2))->method('resolveConfigurationProviders')
			->will($this->returnValue(array($provider1, $provider2)));
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', array('getSingle'));
		$recordService->expects($this->exactly(2))->method('getSingle')->will($this->returnValue($emulatedPageAndContentRecord));
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
				'getDefaultValues', 'getWhiteAndBlackListsFromPageAndContentColumn',
				'applyDefaultValues', 'applyWhitelist', 'applyBlacklist', 'trimItems'
			)
		);
		$GLOBALS['TYPO3_DB'] = $this->getMock(
			'TYPO3\\CMS\\Core\\Database\\DatabaseConnection',
			array('exec_SELECTgetSingleRow'),
			array(), '', FALSE
		);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetSingleRow')->willReturn(NULL);
		$lists = array(array(), array());
		$instance->expects($this->once())->method('getWhiteAndBlackListsFromPageAndContentColumn')->will($this->returnValue($lists));
		$instance->expects($this->once())->method('applyDefaultValues')->will($this->returnValue($items));
		$instance->expects($this->once())->method('applyWhitelist')->will($this->returnValue($items));
		$instance->expects($this->once())->method('applyBlacklist')->will($this->returnValue($items));
		$instance->expects($this->once())->method('trimItems')->will($this->returnValue($items));
		$instance->expects($this->once())->method('getDefaultValues')->will($this->returnValue($defaultValues));
		$controller = $this->getMock(NewContentElementController::class, array('init'), array(), '', FALSE);
		$instance->manipulateWizardItems($items, $controller);
		$this->assertNotEmpty($items);
	}

	/**
	 * @dataProvider getAreaNameAndParentFromRelativeRecordOrDefaults
	 * @param integer $relativeUid
	 * @param array $expected
	 */
	public function testGetAreaNameAndParentFromRelativeRecordOrDefaults($relativeUid, array $expected) {
		$defaults = array('tx_flux_column' => 'defaultarea', 'tx_flux_parent' => 999);
		$inRecord = array('tx_flux_column' => 'recordarea', 'tx_flux_parent' => 111);
		$recordService = $this->getMock('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService', array('getSingle'));
		$recordService->expects($this->any())->method('getSingle')->willReturn($inRecord);
		$instance = $this->getMock('FluidTYPO3\\Flux\\Hooks\\WizardItemsHookSubscriber', array('getDefaultValues'));
		$instance->expects($this->once())->method('getDefaultValues')->willReturn($defaults);
		$instance->injectRecordService($recordService);
		$result = $this->callInaccessibleMethod($instance, 'getAreaNameAndParentFromRelativeRecordOrDefaults', $relativeUid);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getAreaNameAndParentFromRelativeRecordOrDefaults() {
		return array(
			array(0, array(999, 'defaultarea')),
			array(1, array(999, 'defaultarea')),
			array(-1, array(111, 'recordarea'))
		);
	}

}
