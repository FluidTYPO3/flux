<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\BackendLayoutDataProvider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayoutCollection;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class BackendLayoutDataProviderTest
 */
class BackendLayoutDataProviderTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(BackendLayoutDataProvider::class);
        $this->assertAttributeInstanceOf(ObjectManager::class, 'objectManager', $instance);
        $this->assertAttributeInstanceOf(FluxService::class, 'configurationService', $instance);
        $this->assertAttributeInstanceOf(WorkspacesAwareRecordService::class, 'recordService', $instance);
    }

    /**
     * @return void
     */
    public function testGetBackendLayout()
    {
        $instance = new BackendLayoutDataProvider();
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
        $this->assertEquals('grid', $result->getIdentifier());
    }

    /**
     * @return void
     */
    public function testAddBackendLayouts()
    {
        $instance = new BackendLayoutDataProvider();
        $collection = new BackendLayoutCollection('collection');
        $context = new DataProviderContext();
        $context->setPageId(1);
        $instance->addBackendLayouts($context, $collection);
        $this->assertInstanceOf(BackendLayout::class, reset($collection->getAll()));
    }
}
