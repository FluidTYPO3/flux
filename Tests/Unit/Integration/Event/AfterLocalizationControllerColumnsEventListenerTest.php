<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Event\AfterLocalizationControllerColumnsEventListener;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\Event\AfterPageColumnsSelectedForLocalizationEvent;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;

class AfterLocalizationControllerColumnsEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(AfterPageColumnsSelectedForLocalizationEvent::class)) {
            self::markTestSkipped('Event implementation not available on current TYPO3 version');
        }

        parent::setUp();
    }

    /**
     * @dataProvider getTestModifyColumnsManifestTestValues
     */
    public function testModifyColumnsManifest(array $expectedColumns, array $expectedColumnList, array $records): void
    {
        $columns = [];
        $columnList = [];
        $backendLayout = $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock();
        $subject = new AfterLocalizationControllerColumnsEventListener();
        $event = new AfterPageColumnsSelectedForLocalizationEvent(
            $columns,
            $columnList,
            $backendLayout,
            $records,
            []
        );
        $subject->modifyColumnsManifest($event);
        self::assertSame($expectedColumns, $event->getColumns(), 'Columns do not match');
        self::assertSame($expectedColumnList, $event->getColumnList(), 'Column list does not match');
    }

    public function getTestModifyColumnsManifestTestValues(): array
    {
        return [
            'without records' => [[], [], []],
            'records only in page columns' => [[], [], [['uid' => 123, 'colPos' => 1]]],
            'records in nested content' => [[12301 => 'Nested'], ['12301'], [['uid' => 123, 'colPos' => 12301]]],
        ];
    }
}
