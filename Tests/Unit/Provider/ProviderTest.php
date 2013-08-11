<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Provider_ProviderTest extends Tx_Flux_Provider_AbstractProviderTest {

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
		$record = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', array(), 'flux');
		$this->assertInstanceOf('Tx_Flux_Provider_ProviderInterface', $provider);
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertNotEmpty($extensionKey);
		$this->assertRegExp('/[a-z_]+/', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canReturnPathSetByRecordWithoutParentAndWithoutChildren() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', $row);
		$this->assertInstanceOf('Tx_Flux_Provider_ProviderInterface', $provider);
		$paths = $provider->getTemplatePaths($row);
		$this->assertIsArray($paths);
	}

	/**
	 * @test
	 */
	public function canCreateFormFromDefinitionWithAllSupportedNodes() {
		/** @var Tx_Flux_Provider_ProviderInterface $instance */
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->loadSettings($this->definition);
		$form = $provider->getForm($record);
		$this->assertInstanceOf('Tx_Flux_Form', $form);
	}

	/**
	 * @test
	 */
	public function canCreateGridFromDefinitionWithAllSupportedNodes() {
		/** @var Tx_Flux_Provider_ProviderInterface $instance */
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->loadSettings($this->definition);
		$grid = $provider->getGrid($record);
		$this->assertInstanceOf('Tx_Flux_Form_Container_Grid', $grid);
	}

}
