<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\WizardInterface;

/**
 * SelectTest
 */
class SelectTest extends AbstractWizardTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'hideParent' => false,
        'mode' => 'append',
        'items' => array('dummy' => 'Value', 'dummy2' => 'Value 2')
    );

    /**
     * @test
     */
    public function addsParentNameToOwnNameWhenParentExists()
    {
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
    public function canUseTraversableAsItemsList()
    {
        $items = new \ArrayIterator($this->chainProperties['items']);
        $instance = $this->createInstance();
        $fetched = $instance->setItems($items)->getItems();
        $this->assertInstanceOf('ArrayIterator', $fetched);
        $this->assertIsArray($instance->getFormattedItems());
    }

    /**
     * @test
     */
    public function canUseCommaSeparatedStringAsItemsList()
    {
        $items = implode(',', array_keys($this->chainProperties['items']));
        $instance = $this->createInstance();
        $fetched = $instance->setItems($items)->getItems();
        $this->assertIsString($fetched);
        $this->assertIsArray($instance->getFormattedItems());
    }

    /**
     * @test
     */
    public function canUseSemiColonSeparatedStringAsItemsList()
    {
        $items = 'dummy,Value;dummy2,Value 2';
        $instance = $this->createInstance();
        $fetched = $instance->setItems($items)->getItems();
        $this->assertIsString($fetched);
        $this->assertIsArray($instance->getFormattedItems());
    }
}
