<?php
namespace FluidTYPO3\Flux\Tests\Unit\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;

/**
 * @package Flux
 */
class ProviderTest extends AbstractProviderTest {

	/**
	 * @var array
	 */
	protected $definition = [
		'name' => 'test',
		'label' => 'Test provider',
		'tableName' => 'tt_content',
		'fieldName' => 'pi_flexform',
		'form' => [
			'sheets' => [
				'foo' => [
					'fields' => [
						'test' => [
							'type' => 'Input',
						]
					]
				],
				'bar' => [
					'fields' => [
						'test2' => [
							'type' => 'Input',
						]
					]
				],
			],
			'fields' => [
				'test3' => [
					'type' => 'Input',
				]
			],
		],
		'grid' => [
			'rows' => [
				'foo' => [
					'columns' => [
						'bar' => [
							'areas' => [

							]
						]
					]
				]
			]
		]
	];

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
		$provider = new Provider();
		$provider->setExtensionKey('test');
		$resolver = $this->getMock('FluidTYPO3\\Flux\\Provider\\ProviderResolver', ['resolvePrimaryConfigurationProvider']);
		$resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
		$service->injectProviderResolver($resolver);
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', [], 'flux');
		$this->assertSame($provider, $result);
		$extensionKey = $result->getExtensionKey($record);
		$this->assertNotEmpty($extensionKey);
		$this->assertRegExp('/[a-z_]+/', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canReturnPathSetByRecordWithoutParentAndWithoutChildren() {
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$service = $this->createFluxServiceInstance();
		$provider = new Provider();
		$provider->setTemplatePaths([]);
		$resolver = $this->getMock('FluidTYPO3\\Flux\\Provider\\ProviderResolver', ['resolvePrimaryConfigurationProvider']);
		$resolver->expects($this->once())->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
		$service->injectProviderResolver($resolver);
		$result = $service->resolvePrimaryConfigurationProvider('tt_content', 'pi_flexform', $row);
		$this->assertSame($result, $provider);
		$paths = $result->getTemplatePaths($row);
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

}
