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
abstract class Tx_Flux_Tests_Functional_Form_Field_AbstractFieldTest extends Tx_Flux_Tests_Functional_Form_AbstractFormTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'enable' => TRUE);

	/**
	 * @test
	 */
	public function canGetAndSetStopInheritance() {
		$instance = $this->canChainAllChainableSetters();
		$this->assertFalse($instance->setStopInheritance(FALSE)->getStopInheritance());
		$this->assertTrue($instance->setStopInheritance(TRUE)->getStopInheritance());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canGetAndSetInheritEmpty() {
		$instance = $this->canChainAllChainableSetters();
		$this->assertFalse($instance->setInheritEmpty(FALSE)->getInheritEmpty());
		$this->assertTrue($instance->setInheritEmpty(TRUE)->getInheritEmpty());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canGetAndSetInherit() {
		$instance = $this->canChainAllChainableSetters();
		$this->assertFalse($instance->setInherit(FALSE)->getInherit());
		$this->assertTrue($instance->setInherit(TRUE)->getInherit());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canUseClearableProperty() {
		$instance = $this->canChainAllChainableSetters();
		$this->assertFalse($instance->setClearable(FALSE)->getClearable());
		$this->assertTrue($instance->setClearable(TRUE)->getClearable());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canUseWizards() {
		$instance = $this->canChainAllChainableSetters();
		$wizard = $instance->createWizard('Add', 'add');
		$added = $instance->add($wizard);
		$this->assertSame($added, $instance);
		$fetched = $instance->get('add');
		$bad = $instance->get('bad');
		$this->assertFalse($bad);
		$this->assertSame($fetched, $wizard);
		$removed = $instance->remove('add');
		$this->assertSame($removed, $wizard);
		$bad = $instance->remove('bad');
		$this->assertTrue(FALSE === $bad);
		$instance->add($wizard);
		$built = $this->performTestBuild($instance);
		$this->assertIsArray($built);
	}

}
