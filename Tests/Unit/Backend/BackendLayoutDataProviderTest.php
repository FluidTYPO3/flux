<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\BackendLayoutDataProvider;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;

/**
 * Class BackendLayoutDataProviderTest
 */
class BackendLayoutDataProviderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testGetBackendLayout()
    {
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(['getSingle'])->disableOriginalConstructor()->getMock();
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)->setMethods(['createBackendLayoutInstance'])->disableOriginalConstructor()->getMock();
        $instance->method('createBackendLayoutInstance')->willReturn($this->getMockBuilder(BackendLayout::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock());
        $instance->injectWorkspacesAwareRecordService($recordService);
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
    }

    /**
     * @return void
     */
    public function testAddBackendLayouts()
    {
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(['getSingle'])->disableOriginalConstructor()->getMock();
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)->setMethods(['createBackendLayoutInstance'])->disableOriginalConstructor()->getMock();
        $instance->method('createBackendLayoutInstance')->willReturn($this->getMockBuilder(BackendLayout::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock());
        $instance->injectWorkspacesAwareRecordService($recordService);
        $collection = new BackendLayoutCollection('collection');
        $context = new DataProviderContext();
        $context->setPageId(1);
        $instance->addBackendLayouts($context, $collection);
        $all = $collection->getAll();
        $this->assertInstanceOf(BackendLayout::class, reset($all));
    }
}
