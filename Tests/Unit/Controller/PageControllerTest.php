<?php
namespace FluidTYPO3\Flux\Tests\Unit\Controller;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\PageController;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PageControllerTest
 */
class PageControllerTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testGetRecordReadsFromTypoScriptFrontendController()
    {
        $GLOBALS['TSFE'] = (object) ['page' => ['foo' => 'bar']];
        /** @var PageController $subject */
        $subject = $this->getMockBuilder(PageController::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $record = $subject->getRecord();
        $this->assertSame(['foo' => 'bar'], $record);
    }

    public function testInitializeProvider()
    {
        /** @var FluxService|MockObject $pageConfigurationService */
        $pageConfigurationService = $this->getMockBuilder(
                FluxService::class
            )->setMethods(
                ['resolvePrimaryConfigurationProvider']
            )->disableOriginalConstructor()
            ->getMock();
        /** @var PageService $pageService */
        $pageService = $this->getMockBuilder(
                PageService::class
            )->setMethods(
                ['getPageTemplateConfiguration']
            )->disableOriginalConstructor()
            ->getMock();
        $pageConfigurationService->expects($this->once())->method('resolvePrimaryConfigurationProvider');
        /** @var PageController|MockObject $instance */
        $instance = $this->getMockBuilder(PageController::class)->setMethods(['getRecord'])->disableOriginalConstructor()->getMock();
        $instance->expects($this->once())->method('getRecord')->willReturn([]);
        $instance->injectpageConfigurationService($pageConfigurationService);
        $instance->injectPageService($pageService);
        $this->callInaccessibleMethod($instance, 'initializeProvider');
    }
}
