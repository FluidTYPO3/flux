<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\Form\Field\AbstractFieldTest;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;

/**
 * SelectTest
 */
class SelectTest extends AbstractFieldTest
{

    /**
     * @var array
     */
    protected $chainProperties = array(
        'name' => 'test',
        'label' => 'Test field',
        'itemListStyle' => 'color: red',
        'selectedListStyle' => 'color: blue',
        'emptyOption' => false,
        'minItems' => 1,
        'maxItems' => 3,
        'requestUpdate' => true,
    );

    /**
     * @test
     */
    public function canConsumeCommaSeparatedItems()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $instance->setItems('1,2');
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canConsumeSingleDimensionalArrayItems()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $instance->setItems(array(1, 2));
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canConsumeMultiDimensionalArrayItems()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $items = array(
            array('foo' => 'bar'),
            array('baz' => 'bay')
        );
        $instance->setItems($items);
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canConsumeQueryObjectItems()
    {
        $GLOBALS['TCA']['foobar']['ctrl']['label'] = 'username';
        /** @var Select $instance */
        $instance = $this->objectManager->get($this->createInstanceClassName());
        $query = $this->getMockBuilder('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Query')->setMethods(array('execute', 'getType'))->disableOriginalConstructor()->getMock();
        $query->expects($this->any())->method('getType')->will($this->returnValue('foobar'));
        $query->expects($this->any())->method('execute')->will($this->returnValue(array(
            new FrontendUser('user1'),
            new FrontendUser('user2')
        )));
        $instance->setItems($query);
        $result = $instance->getItems();
        $this->assertIsArray($result);
        $this->assertEquals(array(
            array('user1', null), array('user2', null)
        ), $result);
    }

    /**
     * @test
     */
    public function getLabelPropertyNameTranslatesTableNameFromObjectTypeRespectingTableMapping()
    {
        $table = 'foo';
        $type = 'bar';
        $fixture = array(
            'config' => array(
                'tx_extbase' => array(
                    'persistence' => array(
                        'classes' => array(
                            $type => array(
                                'mapping' => array(
                                    'tableName' => $table . 'suffix'
                                )
                            )
                        )
                    )
                )
            )
        );
        $service = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('getAllTypoScript'))->getMock();
        $service->expects($this->once())->method('getAllTypoScript')->willReturn($fixture);
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getConfigurationService'))->getMock();
        $instance->expects($this->once())->method('getConfigurationService')->willReturn($service);
        $GLOBALS['TCA'][$table . 'suffix']['ctrl']['label'] = $table . 'label';
        $propertyName = $this->callInaccessibleMethod($instance, 'getLabelPropertyName', $table, $type);
        $this->assertEquals($table . 'label', $propertyName);
    }


    /**
     * @test
     */
    public function translateCsvItems()
    {
        $instance = $this->createInstance();
        $instance->setExtensionName('flux');

        $form = $this->objectManager->get('FluidTYPO3\Flux\Form');
        $form->add($instance);
        $form->setName('parent');
        $instance->setName('child');
        $form->add($instance);

        $instance->setItems('foo,bar');
        $this->assertEquals(array(array('foo', 'foo'), array('bar', 'bar')), $instance->getItems());
        $instance->setTranslateCsvItems(true);

        $expected = array(
            array('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.parent.fields.child.option.foo', 'foo'),
            array('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.parent.fields.child.option.bar', 'bar')
        );

        $this->assertEquals($expected, $instance->getItems());
    }
}
