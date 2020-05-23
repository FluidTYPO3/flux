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
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)->onlyMethods(['resolveProvider'])->disableOriginalConstructor()->getMock();
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->onlyMethods(['getSingle'])->getMock();
        $recordService->expects(self::once())->method('getSingle')->willReturn(null);
        $instance->injectWorkspacesAwareRecordService($recordService);
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
        $this->assertEquals('grid', $result->getIdentifier());
    }

    /**
     * @return void
     */
    public function testAddBackendLayouts()
    {
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)->onlyMethods(['resolveProvider'])->disableOriginalConstructor()->getMock();
        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->onlyMethods(['getSingle'])->getMock();
        $recordService->expects(self::once())->method('getSingle')->willReturn(null);
        $instance->injectWorkspacesAwareRecordService($recordService);
        $collection = new BackendLayoutCollection('collection');
        $context = new DataProviderContext();
        $context->setPageId(1);
        $instance->addBackendLayouts($context, $collection);
        $all = $collection->getAll();
        $this->assertInstanceOf(BackendLayout::class, reset($all));
    }
}
