<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Tests\Unit\Form\AbstractFormTest;

/**
 * @package Flux
 */
abstract class AbstractContainerTest extends AbstractFormTest {

	/**
	 * @var array
	 */
	protected $chainProperties = ['name' => 'test', 'label' => 'Test field', 'transform' => 'string'];

	/**
	 * @return ContainerInterface
	 */
	protected function createInstance() {
		$className = $this->getObjectClassName();
		$instance = $this->objectManager->get($className);
		return $instance;
	}

	/**
	 * @test
	 */
	public function returnsFalseIfChildObjectNameDoesNotExist() {
		$instance = $this->createInstance();
		$result = $instance->get('doesNotExist');
		$this->assertSame(FALSE, $result);
	}

	/**
	 * @test
	 */
	public function canGetAndSetInheritEmpty() {
		$instance = $this->createInstance();
		$instance->setInheritEmpty(TRUE);
		$this->assertEquals(TRUE, $instance->getInheritEmpty());
	}

	/**
	 * @test
	 */
	public function canGetAndSetInherit() {
		$instance = $this->createInstance();
		$instance->setInherit(FALSE);
		$this->assertEquals(FALSE, $instance->getInherit());
	}

	/**
	 * @test
	 */
	public function returnsFalseIfChildObjectNameDoesNotExistRecursively() {
		$instance = $this->createInstance();
		$subContainer = $instance->createContainer('Container', 'testcontainer');
		$subField = $instance->createField('Input', 'test');
		$subContainer->add($subField);
		$instance->add($subContainer);
		$result = $instance->get('doesNotExist', TRUE);
		$this->assertSame(FALSE, $result);
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinitionContainingFields() {
		$properties = [$this->chainProperties];
		$properties['fields'] = [
			'foo' => [
				'type' => 'Input'
			],
			'bar' => [
				'type' => 'Input'
			],
		];
		$instance = call_user_func_array([$this->getObjectClassName(), 'create'], [$properties]);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
	}

}
