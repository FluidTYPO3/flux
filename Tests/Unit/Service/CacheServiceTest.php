<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class CacheServiceTest extends AbstractTestCase
{
    public function testSetInCaches(): void
    {
        $runtimeCache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();
        $persistentCache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();

        $runtimeCache->expects(self::once())->method('set')->with('flux-ec10e0c7a344da191700ab4ace1a5e26', 'foobar');
        $persistentCache->expects(self::once())->method('set')->with('flux-ec10e0c7a344da191700ab4ace1a5e26', 'foobar');

        $subject = new CacheService($persistentCache, $runtimeCache);

        $subject->setInCaches('foobar', true, 'a', 'b', 'c');
    }

    public function testGetFromCaches(): void
    {
        $runtimeCache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();
        $persistentCache = $this->getMockBuilder(FrontendInterface::class)->getMockForAbstractClass();

        $runtimeCache->expects(self::once())
            ->method('get')
            ->with('flux-ec10e0c7a344da191700ab4ace1a5e26')
            ->willReturn(false);
        $persistentCache->expects(self::once())
            ->method('get')
            ->with('flux-ec10e0c7a344da191700ab4ace1a5e26')
            ->willReturn('foobar');

        $subject = new CacheService($persistentCache, $runtimeCache);

        $output = $subject->getFromCaches('a', 'b', 'c');
        self::assertSame('foobar', $output);
    }
}
