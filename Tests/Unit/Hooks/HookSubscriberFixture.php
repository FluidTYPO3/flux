<?php
namespace FluidTYPO3\Flux\Tests\Unit\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookSubscriberInterface;

class HookSubscriberFixture implements HookSubscriberInterface
{
    public function trigger(string $hook, array $data): array
    {
        return $data;
    }
}
