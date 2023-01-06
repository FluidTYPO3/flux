<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\BackendLayoutRenderer;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\PageLayoutContext;

class BackendLayoutRendererTest extends AbstractTestCase
{
    public function testCarriesTransferredContext(): void
    {
        $context = $this->getMockBuilder(PageLayoutContext::class)->disableOriginalConstructor()->getMock();
        $subject = $this->getMockBuilder(BackendLayoutRenderer::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->setContext($context);
        self::assertSame($context, $subject->getContext());
    }
}
