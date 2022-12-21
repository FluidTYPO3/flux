<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\NormalizedData;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation;
use FluidTYPO3\Flux\Integration\NormalizedData\ImplementationRegistry;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ImplementationRegistryTest extends AbstractTestCase
{
    public function testRegistration(): void
    {
        FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
        ImplementationRegistry::registerImplementation(FlexFormImplementation::class, ['foo' => 'bar']);
        ImplementationRegistry::registerImplementation(FlexFormImplementation::class, ['foo' => 'bar']);
        self::assertSame([], ImplementationRegistry::resolveImplementations('pages', 'uid', ['uid' => 123]));

        $resolved = ImplementationRegistry::resolveImplementations('tt_content', 'pi_flexform', ['uid' => 123]);
        self::assertInstanceOf(FlexFormImplementation::class, reset($resolved));
    }
}
