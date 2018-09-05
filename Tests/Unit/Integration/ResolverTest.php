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

/**
 * ResolveUtilityTest
 */
class ResolveUtilityTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function returnsClassIfClassExists()
    {
        $resolver = new Resolver();
        $className = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('FluidTYPO3.Flux', 'Content');
        $instance = $this->objectManager->get($className);
        $this->assertInstanceOf(AbstractFluxController::class, $instance);
    }

    /**
     * @test
     */
    public function returnsNullIfControllerClassNameDoesNotExist()
    {
        $resolver = new Resolver();
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('FluidTYPO3.Flux', 'Void');
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function throwsExceptionForClassIfSetToHardFail()
    {
        $resolver = new Resolver();
        $this->expectExceptionCode(1364498093);
        $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('FluidTYPO3.Flux', 'Void', true);
    }

    /**
     * @test
     */
    public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassExists()
    {
        $resolver = new Resolver();
        class_alias(AbstractFluxController::class, 'Void\NoName\Controller\FakeController');
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('Void.NoName', 'Fake');
        $this->assertSame('Void\NoName\Controller\FakeController', $result);
    }

    /**
     * @test
     */
    public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassDoesNotExist()
    {
        $resolver = new Resolver();
        $result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndControllerName('Void.NoName', 'Void');
        $this->assertNull($result);
    }
}
