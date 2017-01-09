<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Tests\Unit\Form\AbstractFormTest;

/**
 * AbstractFieldTest
 */
abstract class AbstractFieldTest extends AbstractFormTest
{

    /**
     * @var array
     */
    protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'enable' => true);

    /**
     * @test
     */
    public function canGetAndSetInheritEmpty()
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setInheritEmpty(false)->getInheritEmpty());
        $this->assertTrue($instance->setInheritEmpty(true)->getInheritEmpty());
    }

    /**
     * @test
     */
    public function canGetAndSetInherit()
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setInherit(false)->getInherit());
        $this->assertTrue($instance->setInherit(true)->getInherit());
    }

    /**
     * @test
     */
    public function canUseClearableProperty()
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setClearable(false)->getClearable());
        $this->assertTrue($instance->setClearable(true)->getClearable());
    }

    /**
     * @test
     */
    public function returnsEmptyArrayForDisabledVersionOfField()
    {
        $instance = $this->canChainAllChainableSetters();
        $instance->setEnable(false);
        $built = $instance->build();
        $this->assertIsArray($built);
        $this->assertSame(0, count($built));
    }

    /**
     * @test
     */
    public function returnsEmptyLabelIfFormExtensionNameIsEmpty()
    {
        $instance = $this->createInstance();
        /** @var Form $form */
        $form = $this->objectManager->get('FluidTYPO3\Flux\Form');
        $form->add($instance);
        $form->setExtensionName(null);
        $this->assertEmpty($form->getLabel());
    }

    /**
     * @test
     */
    public function returnsEmptyLabelIfFormExtensionNameIsNotLoaded()
    {
        $instance = $this->createInstance();
        /** @var Form $form */
        $form = $this->objectManager->get('FluidTYPO3\Flux\Form');
        $form->add($instance);
        $form->setExtensionName('void');
        $this->assertEmpty($form->getLabel());
    }

    /**
     * @test
     */
    public function canUseWizards()
    {
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
        $this->assertTrue(false === $bad);
        $instance->add($wizard);
        $built = $this->performTestBuild($instance);
        $this->assertIsArray($built);
        $this->assertTrue($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canCreateFromDefinition()
    {
        $properties = $this->chainProperties;
        $class = $this->getObjectClassName();
        $properties['type'] = implode('/', array_slice(explode('\\', $class), 4, 1));
        ;
        $instance = call_user_func_array(array($class, 'create'), array($properties));
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidFieldTypeWhenCreatingFromDefinition()
    {
        $properties = $this->chainProperties;
        $properties['type'] = 'InvalidType';
        $this->setExpectedException('RuntimeException', '', 1375373527);
        call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
    }

    /**
     * @test
     */
    public function canCreateFromSettingsUsingFullClassName()
    {
        $properties = $this->chainProperties;
        $properties['type'] = $this->getObjectClassName();
        $instance = call_user_func_array(array($this->getObjectClassName(), 'create'), array($properties));
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
    }

    /**
     * @test
     */
    public function canCreateSectionUsingShortcutMethod()
    {
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
    public function prefixesParentObjectNameToAutoLabelIfInsideObject()
    {
        $instance = $this->createInstance();
        $parent = Form\Container\Object::create();
        $parent->setName('parent');
        $instance->setName('child');
        $parent->add($instance);
        $output = $instance->getLabel();
        $this->assertContains('parent.child', $output);
    }

    /**
     * @test
     */
    public function canBuildWithClearableFlag()
    {
        $instance = $this->createInstance();
        $instance->setClearable(true);
        $result = $this->performTestBuild($instance);
        $this->assertNotEmpty($result['config']['wizards']);
    }

    /**
     * @test
     */
    public function modifyCreatesWizards()
    {
        $form = Form::create();
        $field = $form->createField('Input', 'testfield');
        $this->assertFalse($field->has('add'));
        $field->modify(array('wizards' => array('test' => array('type' => 'Add', 'name' => 'add', 'label' => 'Test'))));
        $this->assertTrue($field->has('add'));
    }

    /**
     * @test
     */
    public function modifyModifiesWizards()
    {
        $form = Form::create();
        $field = $form->createField('Input', 'testfield');
        $wizard = $field->createWizard('Add', 'add', 'Original label');
        $field->modify(array('wizards' => array('test' => array('type' => 'Add', 'name' => 'add', 'label' => 'Test'))));
        $this->assertEquals('Test', $wizard->getLabel());
    }
}
