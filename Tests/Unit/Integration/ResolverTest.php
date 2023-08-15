<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Integration\Resolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ResolverTest extends AbstractTestCase
{
    public function testReturnsClassIfClassExists(): void
    {
        $resolver = new Resolver();
        $className = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName(
            'FluidTYPO3.Flux',
            'Content'
        );
        $this->assertTrue(class_exists($className));
    }

    public function testReturnsNullIfControllerClassNameDoesNotExist(): void
    {
        $resolver = new Resolver();
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('FluidTYPO3.Flux', 'Void');
        $this->assertNull($result);
    }

    public function testThrowsExceptionForClassIfSetToHardFail(): void
    {
        $resolver = new Resolver();
        $this->expectExceptionCode(1364498093);
        $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('FluidTYPO3.Flux', 'Void', true);
    }

    public function testCanDetectControllerPresenceFromExtensionKeyAndControllerType(): void
    {
        $resolver = new Resolver();
        class_alias(AbstractFluxController::class, 'Void\NoName\Controller\FakeController');
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('Void.NoName', 'Fake');
        $this->assertSame('Void\NoName\Controller\FakeController', $result);
    }

    public function testCanDetectControllerPresenceFromExtensionKeyAndControllerTypeWhenClassDoesNotExist(): void
    {
        $resolver = new Resolver();
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('Void.NoName', 'Void');
        $this->assertNull($result);
    }
}
