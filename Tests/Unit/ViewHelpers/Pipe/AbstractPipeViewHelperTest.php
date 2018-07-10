<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * AbstractPipeViewHelperTest
 */
class AbstractPipeViewHelperTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function testPreparePipeInstanceDefaultReturnsStandardPipe()
    {
        $className = 'FluidTYPO3\\Flux\\ViewHelpers\\Pipe\\AbstractPipeViewHelper';
        $instance = $this->getMockBuilder($className)->getMock();
        $result = $this->callInaccessibleMethod($instance, 'preparePipeInstance', new RenderingContext(), array());
        $this->assertInstanceOf('FluidTYPO3\\Flux\\Outlet\\Pipe\\StandardPipe', $result);
    }
}
