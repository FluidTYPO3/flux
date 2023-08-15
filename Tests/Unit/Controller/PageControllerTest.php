<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class PageControllerTest extends AbstractTestCase
{
    public function testGetRecordReadsFromTypoScriptFrontendController(): void
    {
        $GLOBALS['TSFE'] = (object) ['page' => ['foo' => 'bar']];
        /** @var PageController $subject */
        $subject = $this->getMockBuilder(PageController::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $record = $subject->getRecord();
        $this->assertSame(['foo' => 'bar'], $record);
    }
}
