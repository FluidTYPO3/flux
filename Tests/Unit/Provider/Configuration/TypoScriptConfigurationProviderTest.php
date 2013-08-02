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
class Tx_Flux_Provider_Configuration_TypoScriptConfigurationProviderTest extends Tx_Flux_Tests_Provider_AbstractConfigurationProviderTest {

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
	public function canCreateFormFromDefinitionWithAllSupportedNodes() {
		/** @var Tx_Flux_Provider_Configuration_TypoScriptConfigurationProvider $provider */
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
		/** @var Tx_Flux_Provider_Configuration_TypoScriptConfigurationProvider $provider */
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$provider->loadSettings($this->definition);
		$grid = $provider->getGrid($record);
		$this->assertInstanceOf('Tx_Flux_Form_Container_Grid', $grid);
	}

	/**
	 * @test
	 */
	public function canGetExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertNull($extensionKey);
	}

	/**
	 * @test
	 */
	public function canGetTableName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$tableName = $provider->getTableName($record);
		$this->assertNull($tableName);
	}

	/**
	 * @test
	 */
	public function canGetName() {
		$provider = $this->getConfigurationProviderInstance();
		$provider->loadSettings($this->definition);
		$this->assertSame($provider->getName(), $this->definition['name']);
	}

}
