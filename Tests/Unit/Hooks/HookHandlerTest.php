<?php
namespace FluidTYPO3\Flux\Tests\Unit\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Hooks\HookSubscriberInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class HookHandlerTest extends AbstractTestCase
{
    public function testSubscribeAndUnsubscribe(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] = [];
        $dummy = $this->getMockBuilder(HookSubscriberInterface::class)->getMockForAbstractClass();
        $dummyClass = get_class($dummy);
        HookHandler::subscribe(HookHandler::PROVIDER_REGISTERED, $dummyClass);
        $this->assertSame(
            $dummyClass,
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][HookHandler::PROVIDER_REGISTERED][$dummyClass]
        );
        $existed = HookHandler::unsubscribe(HookHandler::PROVIDER_REGISTERED, $dummyClass);
        $this->assertTrue($existed);
        $this->assertNotContains(
            $dummyClass,
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][HookHandler::PROVIDER_REGISTERED]
        );
    }

    public function testTriggerWithInvalidHookThrowsException(): void
    {
        $dummy = $this->getMockBuilder(HookHandler::class)->getMock();
        $dummyClass = get_class($dummy);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][HookHandler::PROVIDER_REGISTERED][$dummyClass]
            = $dummyClass;
        $this->expectException(\InvalidArgumentException::class);
        HookHandler::trigger(HookHandler::PROVIDER_REGISTERED);
    }

    public function testTriggerWithValidHookSubscriberReturnsDataArgument(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] = [
            HookHandler::PROVIDER_REGISTERED => [HookSubscriberFixture::class => HookSubscriberFixture::class]
        ];
        $data = ['foo' => 'bar'];
        $returned = HookHandler::trigger(HookHandler::PROVIDER_REGISTERED, $data);
        $this->assertSame($data, $returned);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'] = [];
    }
}
