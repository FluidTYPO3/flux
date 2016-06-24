<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\Form\AbstractFormTest;
use FluidTYPO3\Flux\Form\WizardInterface;

/**
 * AbstractWizardTest
 */
abstract class AbstractWizardTest extends AbstractFormTest
{

    /**
     * @var array
     */
    protected $chainProperties = array('name' => 'test', 'label' => 'Test field', 'hideParent' => false);

    /**
     * @return void
     */
    public function canAutoWriteLabel()
    {

    }

    /**
     * @return void
     */
    public function canGetLabel()
    {

    }

    /**
     * @test
     */
    public function hasChildrenAlwaysReturnsFalse()
    {
        /** @var WizardInterface $instance */
        $instance = $this->createInstance();
        $this->assertFalse($instance->hasChildren());
    }

    /**
     * @test
     */
    public function canRenderWithParentfield()
    {
        /** @var WizardInterface $instance */
        $instance = $this->createInstance();
        $field = $instance->createField('Input', 'test');
        $field->add($instance);
        $this->performTestBuild($instance);
    }

    /**
     * @test
     */
    public function canRenderWithoutParentfield()
    {
        /** @var WizardInterface $instance */
        $instance = $this->createInstance();
        $this->performTestBuild($instance);
    }

    /**
     * @test
     */
    public function canAttachToFields()
    {
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
        $this->assertTrue(false === $bad);
        $field->add($instance);
        $built = $this->performTestBuild($instance);
        $this->assertIsArray($built);
    }
}
