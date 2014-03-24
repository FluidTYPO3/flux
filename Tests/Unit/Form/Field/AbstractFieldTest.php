<?php
namespace FluidTYPO3\Flux\Form\Field;
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

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\AbstractFormTest;
use FluidTYPO3\Flux\Form;

/**
 * author Claus Due <claus@namelesscoder.net>
 * @package Flux
 */
abstract class AbstractFieldTest extends AbstractFormTest {

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
	public function returnsEmptyArrayForDisabledVersionOfField() {
		$instance = $this->canChainAllChainableSetters();
		$instance->setEnable(FALSE);
		$built = $instance->build();
		$this->assertIsArray($built);
		$this->assertSame(0, count($built));
	}

	/**
	 * @test
	 */
	public function returnsEmptyLabelIfFormExtensionNameIsEmpty() {
		$instance = $this->createInstance();
		/** @var Form $form */
		$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
		$form->add($instance);
		$form->setExtensionName(NULL);
		$this->performTestBuild($form);
	}

	/**
	 * @test
	 */
	public function returnsEmptyLabelIfFormExtensionNameIsNotLoaded() {
		$instance = $this->createInstance();
		/** @var Form $form */
		$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
		$form->add($instance);
		$form->setExtensionName('void');
		$this->performTestBuild($form);
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
		$this->assertTrue($instance->hasChildren());
	}

	/**
	 * @test
	 */
	public function canCreateFromDefinition() {
		$properties = $this->chainProperties;
		$class = $this->getObjectClassName();
		$properties['type'] = implode('/', array_slice(explode('\\', $class), 4, 1));;
		$instance = call_user_func_array(array($class, 'create'), array($properties));
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
	}

	/**
	 * @test
	 */
	public function throwsExceptionOnInvalidFieldTypeWhenCreatingFromDefinition() {
		$properties = $this->chainProperties;
		$properties['type'] = 'InvalidType';
		$this->setExpectedException('RuntimeException', NULL, 1375373527);
		call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
	}

	/**
	 * @test
	 */
	public function canCreateFromSettingsUsingFullClassName() {
		$properties = $this->chainProperties;
		$properties['type'] = substr(get_class($this), 0, -4);
		$instance = call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
	}

	/**
	 * @test
	 */
	public function canCreateSectionUsingShortcutMethod() {
		$definition = array(
			'name' => 'test',
			'label' => 'Test section',
			'type' => 'Section'
		);
		$section = AbstractFormField::create($definition);
		$this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Section', $section);
		$this->assertSame($definition['name'], $section->getName());
	}

	/**
	 * @test
	 */
	public function prefixesParentObjectNameToAutoLabelIfInsideObject() {
		$instance = $this->createInstance();
		$parent = Form\Container\Object::create();
		$parent->setName('parent');
		$instance->setName('child');
		$parent->add($instance);
		$output = $instance->getLabel();
		$this->assertContains('parent.child', $output);
	}

}
