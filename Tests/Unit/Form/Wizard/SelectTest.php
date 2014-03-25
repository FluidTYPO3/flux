<?php
namespace FluidTYPO3\Flux\Form\Wizard;
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

use FluidTYPO3\Flux\Form\WizardInterface;

/**
 * @package Flux
 */
class SelectTest extends AbstractWizardTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'name' => 'test',
		'label' => 'Test field',
		'hideParent' => FALSE,
		'mode' => 'append',
		'items' => array('dummy' => 'Value', 'dummy2' => 'Value 2')
	);

	/**
	 * @test
	 */
	public function addsParentNameToOwnNameWhenParentExists() {
		/** @var WizardInterface $instance */
		$instance = $this->createInstance();
		$instance->setName('suffix');
		$this->assertNotContains('prefix', $instance->getName());
		$field = $instance->createField('Input', 'prefix');
		$field->add($instance);
		$this->assertContains('prefix', $instance->getName());
	}

	/**
	 * @test
	 */
	public function canUseTraversableAsItemsList() {
		$items = new \ArrayIterator($this->chainProperties['items']);
		$instance = $this->createInstance();
		$fetched = $instance->setItems($items)->getItems();
		$this->assertIsString('ArrayIterator', $fetched);
		$this->assertIsArray($instance->getFormattedItems());
	}

	/**
	 * @test
	 */
	public function canUseCommaSeparatedStringAsItemsList() {
		$items = implode(',', array_keys($this->chainProperties['items']));
		$instance = $this->createInstance();
		$fetched = $instance->setItems($items)->getItems();
		$this->assertIsString($fetched);
		$this->assertIsArray($instance->getFormattedItems());
	}

	/**
	 * @test
	 */
	public function canUseSemiColonSeparatedStringAsItemsList() {
		$items = 'dummy,Value;dummy2,Value 2';
		$instance = $this->createInstance();
		$fetched = $instance->setItems($items)->getItems();
		$this->assertIsString($fetched);
		$this->assertIsArray($instance->getFormattedItems());
	}

}
