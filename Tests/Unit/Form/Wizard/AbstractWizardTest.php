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

use FluidTYPO3\Flux\Form\AbstractFormTest;
use FluidTYPO3\Flux\Form\WizardInterface;

/**
 * @package Flux
 */
abstract class AbstractWizardTest extends AbstractFormTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'hideParent' => FALSE);

	/**
	 * @return void
	 */
	public function canAutoWriteLabel() {

	}

	/**
	 * @return void
	 */
	public function canGetLabel() {

	}

	/**
	 * @test
	 */
	public function hasChildrenAlwaysReturnsFalse() {
		/** @var WizardInterface $instance */
		$instance = $this->createInstance();
		$this->assertFalse($instance->hasChildren());
	}

	/**
	 * @test
	 */
	public function canRenderWithParentfield() {
		/** @var WizardInterface $instance */
		$instance = $this->createInstance();
		$field = $instance->createField('Input', 'test');
		$field->add($instance);
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canRenderWithoutParentfield() {
		/** @var WizardInterface $instance */
		$instance = $this->createInstance();
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canAttachToFields() {
		/** @var WizardInterface $instance */
		$instance = $this->canChainAllChainableSetters();
		$field = $instance->createField('Input', 'test');
		$added = $field->add($instance);
		$this->assertSame($added, $field);
		$fetched = $field->get($instance->getName());
		$bad = $field->get('bad');
		$this->assertFalse($bad);
		$this->assertSame($fetched, $instance);
		$removed = $field->remove($instance->getName());
		$this->assertSame($removed, $instance);
		$bad = $field->remove('bad');
		$this->assertTrue(FALSE === $bad);
		$field->add($instance);
		$built = $this->performTestBuild($instance);
		$this->assertIsArray($built);
	}

}
