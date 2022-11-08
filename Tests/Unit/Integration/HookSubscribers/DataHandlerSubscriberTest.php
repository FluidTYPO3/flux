<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\HookSubscribers\DataHandlerSubscriber;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;

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

        $dataHandler = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $dataHandler = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $dataHandler = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $dataHandler = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['log'])
            ->disableOriginalConstructor()
            ->getMock();
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

        $dataHandler = $this->getMockBuilder(DataHandler::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
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
}
