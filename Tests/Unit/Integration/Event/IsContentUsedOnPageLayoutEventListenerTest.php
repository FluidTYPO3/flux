<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Event\IsContentUsedOnPageLayoutEventListener;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\Event\IsContentUsedOnPageLayoutEvent;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class IsContentUsedOnPageLayoutEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(IsContentUsedOnPageLayoutEvent::class)) {
            self::markTestSkipped('Skipping test for non-existing event class');
        }
        parent::setUp();
    }

    /**
     * @dataProvider getTestValues
     */
    public function test(bool $expectsUsed, int $colPos): void
    {
        $listener = new IsContentUsedOnPageLayoutEventListener();
        $event = new IsContentUsedOnPageLayoutEvent(
            ['uid' => 123, 'colPos' => $colPos],
            false,
            $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock()
        );
        $listener->handleEvent($event);

        self::assertSame($expectsUsed, $event->isRecordUsed());
    }

    public function getTestValues(): array
    {
        return [
            'does not set used status for non-Flux colPos' => [false, 1],
            'sets used for Flux colPos' => [true, 12301],
        ];
    }
}
