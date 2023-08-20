<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerSubscriberTest extends AbstractTestCase
{
    /**
     * @dataProvider getClearCacheCommandTestValues
     */
    public function testClearCacheCommand(bool $expectsRegenerateMethodCall, array $command): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(['regenerateContentTypes'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($expectsRegenerateMethodCall) {
            $subject->expects(self::once())->method('regenerateContentTypes');
        } else {
            $subject->expects(self::never())->method('regenerateContentTypes');
        }
        $subject->clearCacheCommand($command);
    }

    public function getClearCacheCommandTestValues(): array
    {
        return [
            'with matched command "all"' => [true, ['cacheCmd' => 'all']],
            'with matched command "system"' => [true, ['cacheCmd' => 'system']],
            'with unmatched command' => [false, ['cacheCmd' => 'any']],
        ];
    }

    public function testProcessCommandMapBeforeStartWithContentTypesTable(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(['regenerateContentTypes'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('regenerateContentTypes');

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->cmdmap = [
            'content_types' => [],
        ];

        $subject->processCmdmap_beforeStart($dataHandler);
    }

    public function testProcessCommandMapBeforeStartWithNotContentTable(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(
                [
                    'regenerateContentTypes',
                    'fetchAllColumnNumbersBeneathParent',
                    'cascadeCommandToChildRecords'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::never())->method('regenerateContentTypes');
        $subject->expects(self::never())->method('fetchAllColumnNumbersBeneathParent');
        $subject->expects(self::never())->method('cascadeCommandToChildRecords');

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->cmdmap = [
            'pages' => [],
        ];

        $subject->processCmdmap_beforeStart($dataHandler);
    }

    public function testProcessCommandMapBeforeStartWithUnhandledCommand(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(
                [
                    'regenerateContentTypes',
                    'fetchAllColumnNumbersBeneathParent',
                    'cascadeCommandToChildRecords'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::never())->method('regenerateContentTypes');
        $subject->expects(self::never())->method('fetchAllColumnNumbersBeneathParent');
        $subject->expects(self::never())->method('cascadeCommandToChildRecords');

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->cmdmap = [
            'tt_content' => [
                123 => [
                    'unhandled' => [],
                ],
            ],
        ];

        $subject->processCmdmap_beforeStart($dataHandler);
    }

    public function testProcessCommandMapBeforeStartWithMoveCommandMovingToChildOfSelfLogsProblem(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(['fetchAllColumnNumbersBeneathParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('fetchAllColumnNumbersBeneathParent')->willReturn([1, 2, 3]);

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->expects(self::once())->method('log');
        $dataHandler->cmdmap = [
            'tt_content' => [
                123 => [
                    'move' => [
                        'update' => [
                            'colPos' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $subject->processCmdmap_beforeStart($dataHandler);
    }

    /**
     * @dataProvider getProcessCommandMapBeforeStartCallsCascadeForHandledCommandTestValues
     */
    public function testProcessCommandMapBeforeStartCallsCascadeForHandledCommand(string $command): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->setMethods(['getParentAndRecordsNestedInGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::exactly(3))->method('getParentAndRecordsNestedInGrid')->willReturnOnConsecutiveCalls(
            [null, [['uid' => 123]]],
            [null, [['uid' => 456]]],
            [null, []]
        );

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->cmdmap = [
            'tt_content' => [
                123 => [
                    $command => [
                        'update' => [
                            'colPos' => 1,
                        ],
                    ],
                ],
            ],
        ];

        $subject->processCmdmap_beforeStart($dataHandler);
    }

    public function getProcessCommandMapBeforeStartCallsCascadeForHandledCommandTestValues(): array
    {
        return [
            'delete' => ['delete'],
            'undelete' => ['undelete'],
            'copyToLanguage' => ['copyToLanguage'],
            'localize' => ['localize'],
            'copy' => ['copy'],
        ];
    }

    public function testPreProcessFieldArrayCopiesPageConfigurationToTranslatedVersion(): void
    {
        $originalRecord = [
            'uid' => 1,
            PageProvider::FIELD_NAME_MAIN => 'main-config',
            PageProvider::FIELD_NAME_SUB => 'sub-config',
        ];
        $newRecord = [
            'l10n_source' => 1,
        ];

        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getSingleRecordWithoutRestrictions', 'getProviderResolver'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getSingleRecordWithoutRestrictions')->willReturn($originalRecord);
        $subject->method('getProviderResolver')->willReturn($this->createStub(ProviderResolver::class));

        $subject->processDatamap_preProcessFieldArray(
            $newRecord,
            'pages',
            'NEW123',
            $this->createStub(DataHandler::class)
        );

        self::assertSame(
            $originalRecord[PageProvider::FIELD_NAME_MAIN],
            $newRecord[PageProvider::FIELD_NAME_MAIN],
            'Main field was not copied to copy'
        );
        self::assertSame(
            $originalRecord[PageProvider::FIELD_NAME_SUB],
            $newRecord[PageProvider::FIELD_NAME_SUB],
            'Sub field was not copied to copy'
        );
    }

    public function testPreProcessFieldArrayCopiesTablePrefixedFieldsToRootColumns(): void
    {
        $record = [
            'uid' => 123,
            'field' => [
                'data' => [
                    'options' => [
                        'lDEF' => [
                            'table.subfield' => [
                                'vDEF' => 'value',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $GLOBALS["TCA"]['table']["columns"]['field']["config"]["type"] = 'flex';
        $GLOBALS["TCA"]['table']["columns"]['subfield']["config"]["type"] = 'input';

        $provider = $this->createStub(ProviderInterface::class);

        $resolver = $this->createStub(ProviderResolver::class);
        $resolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getSingleRecordWithoutRestrictions', 'getProviderResolver'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getProviderResolver')->willReturn($resolver);

        $subject->processDatamap_preProcessFieldArray(
            $record,
            'table',
            123,
            $this->createStub(DataHandler::class)
        );

        self::assertSame('value', $record['subfield']);
    }

    public function testPreProcessFieldArrayResolvesMissingColPos(): void
    {
        $record = ['uid' => 123];

        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getSingleRecordWithoutRestrictions', 'getProviderResolver'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getProviderResolver')->willReturn($this->createStub(ProviderResolver::class));
        $subject->method('getSingleRecordWithoutRestrictions')->willReturn(['l18n_parent' => 11, 'colPos' => 22]);

        $dataHandler = $this->createStub(DataHandler::class);
        $dataHandler->datamap['tt_content'][11] = [
            'colPos' => 22,
            'l18n_parent' => 11,
        ];

        $subject->processDatamap_preProcessFieldArray($record, 'tt_content', 123, $dataHandler);

        self::assertSame(22, $record['colPos'] ?? false);
    }

    public function testPostProcessCommandReturnsEarlyForUnmatchedTableAndCommand(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getParentAndRecordsNestedInGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::never())->method('getParentAndRecordsNestedInGrid');

        $dataHandler = $this->createStub(DataHandler::class);

        $update = [];
        $datamap = [];
        $relative = 1;
        $id = 2;

        $table = 'unmatched';
        $command = 'move';
        $subject->processCmdmap_postProcess($command, $table, $id, $relative, $dataHandler, $update, $datamap);

        $table = 'tt_content';
        $command = 'unmatched';
        $subject->processCmdmap_postProcess($command, $table, $id, $relative, $dataHandler, $update, $datamap);
    }

    public function testPostProcessCommandReturnsEarlyWithoutNestedRecords(): void
    {
        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getParentAndRecordsNestedInGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('getParentAndRecordsNestedInGrid')->willReturn([[], []]);

        $dataHandler = $this->createStub(DataHandler::class);

        $update = [];
        $datamap = [];
        $relative = 1;
        $id = 2;
        $table = 'tt_content';
        $command = 'move';
        $subject->processCmdmap_postProcess($command, $table, $id, $relative, $dataHandler, $update, $datamap);
    }

    public function testPostProcessCommandRecursivelyMovesNestedRecordsAfterOther(): void
    {
        $this->executePostProcessRecursiveMoveTest(-6);
    }

    public function testPostProcessCommandRecursivelyMovesNestedRecordsToPage(): void
    {
        $this->executePostProcessRecursiveMoveTest(3);
    }

    private function executePostProcessRecursiveMoveTest(int $relativeTo): void
    {
        $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] = 'sys_language_uid';

        $nestedDataHandler = $this->createStub(DataHandler::class);
        $nestedDataHandler->expects(self::once())->method('start');
        $nestedDataHandler->expects(self::once())->method('process_cmdmap');

        GeneralUtility::addInstance(DataHandler::class, $nestedDataHandler);

        $subject = $this->getMockBuilder(DataHandlerSubscriber::class)
            ->onlyMethods(['getParentAndRecordsNestedInGrid', 'getSingleRecordWithoutRestrictions'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())
            ->method('getParentAndRecordsNestedInGrid')
            ->willReturn([['sys_language_uid' => 3], [['uid' => 12]]]);
        if ($relativeTo < 0) {
            $subject->method('getSingleRecordWithoutRestrictions')->willReturn(['pid' => 5]);
        } else {
            $subject->expects(self::never())->method('getSingleRecordWithoutRestrictions');
        }

        $dataHandler = $this->createStub(DataHandler::class);

        $update = [];
        $datamap = [];
        $id = 2;
        $table = 'tt_content';
        $command = 'move';
        $subject->processCmdmap_postProcess($command, $table, $id, $relativeTo, $dataHandler, $update, $datamap);
    }
}
