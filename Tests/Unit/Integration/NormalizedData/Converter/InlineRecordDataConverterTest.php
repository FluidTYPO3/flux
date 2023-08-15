<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData\Converter;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\NormalizedData\Converter\InlineRecordDataConverter;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InlineRecordDataConverterTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform'] = [
            'config' => [
                'type' => 'flex',
            ],
        ];
    }

    public function testConvertData(): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['fetchConfigurationRecords', 'fetchFieldData'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('fetchConfigurationRecords')->willReturn([['uid' => 1], ['uid' => 2]]);
        $subject->expects(self::exactly(2))->method('fetchFieldData')->willReturnOnConsecutiveCalls(
            ['foo' => 'bar'],
            ['baz' => 'bar'],
        );

        $data = ['old' => 'foo'];
        $result = $subject->convertData($data);

        self::assertSame(
            [
                'old' => 'foo',
                'foo' => 'bar',
                'baz' => 'bar',
            ],
            $result
        );
    }

    public function testConvertStructureReturnsUntouchedStructureWithoutSource(): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['resolveDataSourceDefinition'])
            ->setConstructorArgs(['tt_content', 'pi_flexform', ['uid' => 123, 'pi_flexform' => '']])
            ->getMock();
        $subject->method('resolveDataSourceDefinition')->willReturn(null);

        $structure = ['foo' => 'bar'];

        $output = $subject->convertStructure($structure);

        self::assertSame($structure, $output);
    }

    public function testConvertStructureReturnsUntouchedStructureWithoutSheets(): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['resolveDataSourceDefinition'])
            ->setConstructorArgs(['tt_content', 'pi_flexform', ['uid' => 123, 'pi_flexform' => '']])
            ->getMock();
        $subject->method('resolveDataSourceDefinition')->willReturn([]);

        $structure = ['foo' => 'bar'];

        $output = $subject->convertStructure($structure);

        self::assertSame($structure, $output);
    }

    public function testConvertStructureCallsSynchroniseConfigurationRecords(): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['resolveDataSourceDefinition', 'synchroniseConfigurationRecords'])
            ->setConstructorArgs(['tt_content', 'pi_flexform', ['uid' => 123, 'pi_flexform' => '']])
            ->getMock();
        $subject->method('resolveDataSourceDefinition')->willReturn(['sheets' => ['foo' => []]]);
        $subject->expects(self::once())->method('synchroniseConfigurationRecords');

        $structure = [
            'processedTca' => [
                'columns' => [
                    'pi_flexform' => [
                        'config' => [
                            'type' => 'flex',
                        ],
                    ],
                    'pi_flexform_values' => [
                        'config' => [
                            'type' => 'flex',
                        ],
                    ],
                ],
            ],
        ];
        $expected = $structure;
        $expected['processedTca']['columns']['pi_flexform']['config']
            = $expected['processedTca']['columns']['pi_flexform_values']['config'];

        $output = $subject->convertStructure($structure);

        self::assertSame($expected, $output);
    }

    public function testConvertStructureWithExistingField(): void
    {
        $this->assertConvertStructureBehavior(['test2' => []]);
    }

    public function testConvertStructureWithNewField(): void
    {
        $this->assertConvertStructureBehavior([]);
    }

    private function assertConvertStructureBehavior(array $fieldData): void
    {
        $sheetData = [
            'sheets' => [
                'foo' => [
                    'ROOT' => [
                        'TCEforms' => [
                            'sheetTitle' => 'test',
                        ],
                        'el' => [
                            'test1' => [
                                'type' => 'array',
                            ],
                            'test2' => [
                                'label' => 'Label',
                                'config' => [
                                    'type' => 'select',
                                    'renderType' => 'selectSingle',
                                    'default' => 'default',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $structure = [
            'processedTca' => [
                'columns' => [
                    'pi_flexform' => [
                        'config' => [
                            'type' => 'flex',
                        ],
                    ],
                    'pi_flexform_values' => [
                        'config' => [
                            'type' => 'flex',
                        ],
                    ],
                ],
            ],
        ];

        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(
                [
                    'resolveDataSourceDefinition',
                    'fetchSheetRecord',
                    'insertSheetData',
                    'fetchFieldData',
                    'insertFieldData',
                    'updateFieldData',
                ]
            )
            ->setConstructorArgs(['tt_content', 'pi_flexform', ['uid' => 123, 'pi_flexform' => '', 'pid' => 1]])
            ->getMock();
        $subject->method('resolveDataSourceDefinition')->willReturn($sheetData);
        $subject->expects(self::once())->method('fetchSheetRecord')->with('foo')->willReturn(null);
        $subject->expects(self::once())->method('insertSheetData')->willReturn(3);
        $subject->expects(self::once())->method('fetchFieldData')->with(3)->willReturn($fieldData);
        if (isset($fieldData['test2'])) {
            $subject->expects(self::once())->method('updateFieldData');
        } else {
            $subject->expects(self::once())->method('insertFieldData');
        }

        $subject->convertStructure($structure);
    }

    /**
     * @dataProvider getAssertArrayHasKeyTestValues
     */
    public function testAssertArrayHasKey(bool $expected, array $data, string $key): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->callInaccessibleMethod($subject, 'assertArrayHasKey', $data, $key);
        self:self::assertSame($expected, $result);
    }

    public function getAssertArrayHasKeyTestValues(): array
    {
        return [
            'empty array' => [false, [], 'anykey'],
            'key is empty' => [false, ['another_key'], ''],
            'does not have key' => [false, ['another_key'], 'anykey'],
            'dotted path found' => [true, ['a' => ['b' => 'value']], 'a.b'],
            'dotted path not found' => [false, ['a' => ['c' => 'value']], 'a.b'],
        ];
    }

    public function testResolveDataSourceDefinition(): void
    {
        $subject = new InlineRecordDataConverter('tt_content', 'pi_flexform', []);
        $flexFormTools = $this->getMockBuilder(FlexFormTools::class)
            ->setMethods(['getDataStructureIdentifier', 'parseDataStructureByIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $flexFormTools->method('getDataStructureIdentifier')->willReturn('test');
        $flexFormTools->method('parseDataStructureByIdentifier')->with('test')->willReturn(['foo' => 'bar']);
        GeneralUtility::addInstance(FlexFormTools::class, $flexFormTools);

        $structure = [
            'processedTca' => [
                'columns' => [
                    'pi_flexform' => [
                        'config' => [],
                    ],
                ],
            ],
        ];

        self::assertSame(
            ['foo' => 'bar'],
            $this->callInaccessibleMethod($subject, 'resolveDataSourceDefinition', $structure)
        );
    }

    public function testResolveDataSourceDefinitionSwallowsException(): void
    {
        $subject = new InlineRecordDataConverter('tt_content', 'pi_flexform', []);
        $flexFormTools = $this->getMockBuilder(FlexFormTools::class)
            ->setMethods(['getDataStructureIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $flexFormTools->method('getDataStructureIdentifier')->willThrowException(new \RuntimeException('test'));
        GeneralUtility::addInstance(FlexFormTools::class, $flexFormTools);

        $structure = [
            'processedTca' => [
                'columns' => [
                    'pi_flexform' => [
                        'config' => [],
                    ],
                ],
            ],
        ];

        self::assertNull($this->callInaccessibleMethod($subject, 'resolveDataSourceDefinition', $structure));
    }

    /**
     * @dataProvider getAssignVariableByDottedPathTestValues
     */
    public function testAssignVariableByDottedPath(array $expected, array $data, string $key, string $value): void
    {
        $subject = $this->getMockBuilder(InlineRecordDataConverter::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();

        $result = $this->callInaccessibleMethod($subject, 'assignVariableByDottedPath', $data, $key, $value);

        self:self::assertSame($expected, $result);
    }

    public function getAssignVariableByDottedPathTestValues(): array
    {
        return [
            'not dotted path' => [['key' => 'value'], [], 'key', 'value'],
            'existing dotted path' => [['a' => ['b' => 'new value']], ['a' => ['b' => 'value']], 'a.b', 'new value'],
            'new dotted path' => [['a' => ['b' => 'new value']], ['a' => []], 'a.b', 'new value'],
            'new array dotted path' => [['a' => ['b' => 'new value']], [], 'a.b', 'new value'],
        ];
    }
}
