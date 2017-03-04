<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\ViewAwarePipeTrait;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * ViewAwarePipeTraitTest
 */
class ViewAwarePipeTraitTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testSetViewSetsViewProperty()
    {
        $view = $this->getMockBuilder(ViewInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $subject = $this->getMockBuilder(ViewAwarePipeTrait::class)->getMockForTrait();
        $subject->setView($view);
        $this->assertAttributeSame($view, 'view', $subject);
    }
}
