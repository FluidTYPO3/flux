<?php
namespace FluidTYPO3\Flux\Tests\Unit\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\View\PageLayoutView;

/**
 * PageLayoutViewTest
 */
class PageLayoutViewTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testMakesGenerateTtContentDataArrayPublic()
    {
        $instance = new PageLayoutView();
        $result = $instance->generateTtContentDataArray([['uid' => 123]]);
        $this->assertNull($result);
    }

    /**
     * @tet
     */
    public function testSetAndGetPageInfo()
    {
        $instance = new PageLayoutView();
        $info = ['foo' => 'bar'];
        $instance->setPageinfo($info);
        $this->assertAttributeSame($info, 'pageinfo', $instance);
        $result = $instance->getPageinfo();
        $this->assertSame($info, $result);
    }

}
