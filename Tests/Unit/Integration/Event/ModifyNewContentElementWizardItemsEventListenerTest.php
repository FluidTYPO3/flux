<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Event\ModifyNewContentElementWizardItemsEventListener;
use FluidTYPO3\Flux\Integration\WizardItemsManipulator;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;

class ModifyNewContentElementWizardItemsEventListenerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        if (!class_exists(ModifyNewContentElementWizardItemsEvent::class)) {
            $this->markTestSkipped('Skipping test for non-existing event class');
        }
        parent::setUp();
    }

    public function test(): void
    {
        $manipulator = $this->getMockBuilder(WizardItemsManipulator::class)
            ->onlyMethods(['manipulateWizardItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $manipulator->method('manipulateWizardItems')->willReturn(['foo' => 'bar']);

        $event = new ModifyNewContentElementWizardItemsEvent([], ['uid' => 123], 0, 1, 1);

        $subject = new ModifyNewContentElementWizardItemsEventListener($manipulator);
        $subject->manipulateWizardItems($event);

        self::assertSame(['foo' => 'bar'], $event->getWizardItems());
    }
}
