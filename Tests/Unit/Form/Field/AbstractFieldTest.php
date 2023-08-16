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

abstract class AbstractFieldTest extends AbstractFormTest
{
    protected array $chainProperties = ['name' => 'test', 'label' => 'Test field', 'enabled' => true];

    /**
     * @test
     */
    public function canGetAndSetInheritEmpty(): void
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setInheritEmpty(false)->getInheritEmpty());
        $this->assertTrue($instance->setInheritEmpty(true)->getInheritEmpty());
    }

    /**
     * @test
     */
    public function canGetAndSetInherit(): void
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setInherit(false)->getInherit());
        $this->assertTrue($instance->setInherit(true)->getInherit());
    }

    /**
     * @test
     */
    public function canUseClearableProperty(): void
    {
        $instance = $this->canChainAllChainableSetters();
        $this->assertFalse($instance->setClearable(false)->getClearable());
        $this->assertTrue($instance->setClearable(true)->getClearable());
    }

    /**
     * @test
     */
    public function returnsEmptyArrayForDisabledVersionOfField(): void
    {
        $instance = $this->canChainAllChainableSetters();
        $instance->setEnabled(false);
        $built = $instance->build();
        $this->assertIsArray($built);
        $this->assertSame(0, count($built));
    }

    /**
     * @test
     */
    public function returnsEmptyLabelIfFormExtensionNameIsEmpty(): void
    {
        $instance = $this->createInstance();
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $form->add($instance);
        $form->setExtensionName(null);
        $this->assertEmpty($form->getLabel());
    }

    /**
     * @test
     */
    public function canCreateFromDefinition(): void
    {
        $properties = $this->chainProperties;
        $class = $this->getObjectClassName();
        $properties['type'] = implode('/', array_slice(explode('\\', $class), 4, 1));
        $instance = call_user_func_array([$class, 'create'], [$properties]);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\FormInterface', $instance);
    }

    /**
     * @test
     */
    public function throwsExceptionOnInvalidFieldTypeWhenCreatingFromDefinition(): void
    {
        $properties = $this->chainProperties;
        $properties['type'] = 'InvalidType';
        $this->expectExceptionCode(1375373527);
        call_user_func_array([$this->getObjectClassName(), 'create'], [$properties]);
    }

    /**
     * @test
     */
    public function canCreateFromSettingsUsingFullClassName(): void
    {
        $properties = $this->chainProperties;
        $properties['type'] = $this->getObjectClassName();
        $instance = call_user_func_array([$this->getObjectClassName(), 'create'], [$properties]);
        $this->assertInstanceOf(Form\FormInterface::class, $instance);
    }

    /**
     * @test
     */
    public function canCreateSectionUsingShortcutMethod(): void
    {
        $definition = [
            'name' => 'test',
            'label' => 'Test section',
            'type' => 'Section'
        ];
        $section = AbstractFormField::create($definition);
        $this->assertInstanceOf(Form\Container\Section::class, $section);
        $this->assertSame($definition['name'], $section->getName());
    }

    /**
     * @test
     */
    public function prefixesParentObjectNameToAutoLabelIfInsideObject(): void
    {
        $instance = $this->createInstance();
        $parent = Form\Container\SectionObject::create();
        $parent->setName('parent');
        $instance->setName('child');
        $parent->add($instance);
        $output = $instance->getLabel();
        $this->assertStringContainsString('parent.child', $output);
    }

    /**
     * @test
     */
    public function canBuildWithClearableFlag(): void
    {
        $instance = $this->createInstance();
        $instance->setClearable(true);
        $result = $this->performTestBuild($instance);
        $this->assertNotEmpty($result['config']);
    }
}
