<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\TypoScriptService;
use FluidTYPO3\Flux\Tests\Unit\Form\Field\AbstractFieldTest;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;

class SelectTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'itemListStyle' => 'color: red',
        'selectedListStyle' => 'color: blue',
        'emptyOption' => false,
        'minItems' => 1,
        'maxItems' => 3,
        'requestUpdate' => true,
    ];

    /**
     * @test
     */
    public function canAddEmptyOptionWithStringValue()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $instance->setItems('1');
        $instance->setEmptyOption('2');
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canAddEmptyOptionWithArrayValue()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $instance->setItems('1');
        $instance->setEmptyOption(['', 2]);
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canUseRangeNotation()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $instance->setItems('1..10');
        $this->assertSame(10, count($instance->getItems()));
    }

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
        $instance->setItems([1, 2]);
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canConsumeMultiDimensionalArrayItems()
    {
        /** @var Select $instance */
        $instance = $this->createInstance();
        $items = [
            ['foo' => 'bar'],
            ['baz' => 'bay']
        ];
        $instance->setItems($items);
        $this->assertSame(2, count($instance->getItems()));
    }

    /**
     * @test
     */
    public function canConsumeQueryObjectItems()
    {
        if (!class_exists(FrontendUser::class)) {
            $this->markTestSkipped('Skipping test with FrontendUser dependency');
        }

        $GLOBALS['TCA']['foobar']['ctrl']['label'] = 'username';

        $typoScriptService = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getTypoScriptByPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $typoScriptService->method('getTypoScriptByPath')->willReturn([]);

        /** @var Select $instance */
        $instance = $this->getMockBuilder(Select::class)->setMethods(['getTypoScriptService'])->getMock();
        $instance->method('getTypoScriptService')->willReturn($typoScriptService);
        $query = $this->getMockBuilder(Query::class)
            ->onlyMethods(['execute', 'getType'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->any())->method('getType')->will($this->returnValue('foobar'));
        $query->expects($this->any())->method('execute')->will($this->returnValue([
            new FrontendUser('user1'),
            new FrontendUser('user2')
        ]));
        $instance->setItems($query);
        $result = $instance->getItems();
        $this->assertIsArray($result);
        $this->assertEquals([
            ['user1', null], ['user2', null]
        ], $result);
    }

    /**
     * @test
     */
    public function getLabelPropertyNameTranslatesTableNameFromObjectTypeRespectingTableMapping()
    {
        $table = 'foo';
        $type = 'bar';
        $service = $this->getMockBuilder(TypoScriptService::class)
            ->onlyMethods(['getTypoScriptByPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $service->expects($this->once())->method('getTypoScriptByPath')->willReturn($table . 'suffix');
        $instance = $this->getMockBuilder($this->createInstanceClassName())->onlyMethods(['getTypoScriptService'])->getMock();
        $instance->expects($this->once())->method('getTypoScriptService')->willReturn($service);
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

        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $form->add($instance);
        $form->setName('parent');
        $instance->setName('child');
        $form->add($instance);

        $instance->setItems('foo,bar');
        $this->assertEquals([['foo', 'foo'], ['bar', 'bar']], $instance->getItems());
        $instance->setTranslateCsvItems(true);

        $expected = [
            ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.parent.fields.child.option.foo', 'foo'],
            ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.parent.fields.child.option.bar', 'bar']
        ];

        $this->assertEquals($expected, $instance->getItems());
    }
}
