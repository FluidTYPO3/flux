<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\StandardPipe;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Pipe\AbstractPipeViewHelper;

class AbstractPipeViewHelperTest extends AbstractViewHelperTestCase
{
    protected function createInstance()
    {
        return $this->getMockBuilder($this->createInstanceClassName())->getMockForAbstractClass();
    }

    public function testPreparePipeInstanceDefaultReturnsStandardPipe()
    {
        $className = AbstractPipeViewHelper::class;
        $instance = $this->getMockBuilder($className)->getMockForAbstractClass();
        $result = $this->callInaccessibleMethod($instance, 'preparePipeInstance', $this->renderingContext, []);
        $this->assertInstanceOf(StandardPipe::class, $result);
    }
}
