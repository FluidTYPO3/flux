<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use FluidTYPO3\Flux\Integration\Event\BootCompletedEventListener;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BootCompletedEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(BootCompletedEvent::class)) {
            self::markTestSkipped('Event implementation not available on current TYPO3 version');
        }

        parent::setUp();
    }

    public function testSpoolQueuedTcaOperations(): void
    {
        $event = new BootCompletedEvent(false);

        $applicator = $this->getMockBuilder(SpooledConfigurationApplicator::class)
            ->setMethods(['processData'])
            ->disableOriginalConstructor()
            ->getMock();
        $applicator->expects(self::once())->method('processData');
        GeneralUtility::addInstance(SpooledConfigurationApplicator::class, $applicator);

        $subject = new BootCompletedEventListener();
        $subject->spoolQueuedTcaOperations($event);
    }
}
