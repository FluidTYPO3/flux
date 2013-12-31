<?php
namespace FluidTYPO3\Flux\Provider;
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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ProviderTest extends AbstractProviderTest {

	/**
	 * @var array
	 */
	protected $definition = array(
		'name' => 'test',
		'label' => 'Test provider',
		'tableName' => 'tt_content',
		'fieldName' => 'pi_flexform',
		'form' => array(
			'sheets' => array(
				'foo' => array(
					'fields' => array(
						'test' => array(
							'type' => 'Input',
						)
					)
				),
				'bar' => array(
					'fields' => array(
						'test2' => array(
							'type' => 'Input',
						)
					)
				),
			),
			'fields' => array(
				'test3' => array(
					'type' => 'Input',
				)
			),
		),
		'grid' => array(
			'rows' => array(
				'foo' => array(
					'columns' => array(
						'bar' => array(
							'areas' => array(

							)
						)
					)
				)
			)
		)
	);

	/**
	 * @test
	 */
	public function canGetName() {
		$provider = $this->getConfigurationProviderInstance();
		$provider->loadSettings($this->definition);
		$this->assertSame($provider->getName(), $this->definition['name']);
	}

	/**
	 * @test
	 */
	public function canCreateInstanceWithListType() {
		$definition = $this->definition;
		$definition['listType'] = 'felogin_pi1';
		$provider = $this->getConfigurationProviderInstance();
		$provider->loadSettings($definition);
		$this->assertSame($provider->getName(), $definition['name']);
		$this->assertSame($provider->getListType(), $definition['listType']);
	}

	/**
	 * @test
	 */
	public function canReturnExtensionKey() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertInstanceOf('FluidTYPO3\Flux\Provider\ProviderInterface', $provider);
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertNotEmpty($extensionKey);
		$this->assertRegExp('/[a-z_]+/', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canReturnPathSetByRecordWithoutParentAndWithoutChildren() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', $row);
		$this->assertInstanceOf('FluidTYPO3\Flux\Provider\ProviderInterface', $provider);
		$paths = $provider->getTemplatePaths($row);
		$this->assertIsArray($paths);
	}

	/**
	 * @test
	 */
	public function canCreateFormFromDefinitionWithAllSupportedNodes() {
		/** @var ProviderInterface $instance */
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->loadSettings($this->definition);
		$form = $provider->getForm($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form', $form);
	}

	/**
	 * @test
	 */
	public function canCreateGridFromDefinitionWithAllSupportedNodes() {
		/** @var ProviderInterface $instance */
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->loadSettings($this->definition);
		$grid = $provider->getGrid($record);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $grid);
	}

	/**
	 * @test
	 */
	public function dispatchesMessageOnInvalidPathsReturnedFromConfigurationService() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$className = substr(get_class($this), 0, -4);
		$instance = $this->getMock($className, array('getExtensionKey'));
		$instance->expects($this->atLeastOnce())->method('getExtensionKey')->will($this->returnValue('flux'));
		$configurationService = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('message', 'getViewConfigurationForExtensionName'));
		$configurationService->expects($this->once())->method('message');
		$configurationService->expects($this->once())->method('getViewConfigurationForExtensionName')->will($this->returnValue('invalidstring'));
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$instance->getTemplatePaths($row);
	}

	/**
	 * @test
	 */
	public function getParentFieldValueLoadsRecordFromDatabaseIfRecordLacksParentFieldValue() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['uid'] = 2;
		$rowWithPid = $row;
		$rowWithPid['pid'] = 1;
		$className = substr(get_class($this), 0, -4);
		$instance = $this->getMock($className, array('getParentFieldName', 'getTableName', 'loadRecordFromDatabase'));
		$instance->expects($this->once())->method('loadRecordFromDatabase')->with($row['uid'])->will($this->returnValue($rowWithPid));
		$instance->expects($this->once())->method('getParentFieldName')->with($row)->will($this->returnValue('pid'));
		$result = $this->callInaccessibleMethod($instance, 'getParentFieldValue', $row);
		$this->assertEquals($rowWithPid['pid'], $result);
	}

}
