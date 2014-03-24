<?php
namespace FluidTYPO3\Flux\Form\Container;
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

use FluidTYPO3\Flux\Form\AbstractFormTest;
use FluidTYPO3\Flux\Form\ContainerInterface;

/**
 * @package Flux
 */
abstract class AbstractContainerTest extends AbstractFormTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'transform' => 'string');

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
		$properties = array($this->chainProperties);
		$properties['fields'] = array(
			'foo' => array(
				'type' => 'Input'
			),
			'bar' => array(
				'type' => 'Input'
			),
		);
		$instance = call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
	}

}
