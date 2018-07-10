<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Contract for Flux hook subscribers.
 *
 * All hook subscribers implement the same method, only the
 * data differs. You can split handling into several classes
 * if you need separation of concerns, but a single class is
 * theoretically capable of handling every single hook call.
 *
 * Hook subscribers must implement a single method, trigger(),
 * which always receives a string hook name and an array of
 * data, and which must always return the array of data (with
 * or without any modifications). If your hook does not return
 * the exact associative array it received in $data this could
 * cause Flux to fail with fatal errors!
 */
interface HookSubscriberInterface
{
    public function trigger(string $hook, array $data): array;
}
