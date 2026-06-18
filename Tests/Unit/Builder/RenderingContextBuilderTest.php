<?php
namespace FluidTYPO3\Flux\Tests\Unit\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\RenderingContextBuilder;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\RenderingContext;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class RenderingContextBuilderTest extends AbstractTestCase
{
    public function testBuildRenderingContextFor(): void
    {
        $renderingContext = new RenderingContext();

        $subject = $this->getMockBuilder(RenderingContextBuilder::class)
            ->onlyMethods(['createRenderingContextInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('createRenderingContextInstance')->willReturn($renderingContext);

        $output = $subject->buildRenderingContextFor(
            'FluidTYPO3.Flux',
            'Default',
            'default',
            'Default'
        );
        self::assertSame($renderingContext, $output);
    }
}
