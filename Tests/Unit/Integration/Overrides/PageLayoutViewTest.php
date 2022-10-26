<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\Overrides;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Overrides\PageLayoutView;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * PageLayoutViewTest
 */
class PageLayoutViewTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function testSetAndGetPageInfo()
    {
        $instance = $this->getMockBuilder(PageLayoutView::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $info = ['foo' => 'bar'];
        $instance->setPageinfo($info);
        $this->assertSame($info, $instance->getPageinfo());
        $result = $instance->getPageinfo();
        $this->assertSame($info, $result);
    }
}
