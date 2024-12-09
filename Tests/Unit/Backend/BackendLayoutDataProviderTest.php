<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\BackendLayoutDataProvider;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
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
    protected WorkspacesAwareRecordService $recordService;
    protected ProviderResolver $providerResolver;

    protected function setUp(): void
    {
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePageProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testGetBackendLayoutReturnsEmptyLayoutWithoutRecord()
    {
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)
            ->onlyMethods(['createBackendLayoutInstance'])
            ->setConstructorArgs([$this->providerResolver, $this->recordService])
            ->getMock();
        $instance->method('createBackendLayoutInstance')
            ->willReturn(
                $this->getMockBuilder(BackendLayout::class)
                    ->setMethods(['dummy'])
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
    }

    /**
     * @return void
     */
    public function testGetBackendLayoutReturnsEmptyLayoutWithoutRecordInSecondCall()
    {
        $this->recordService->method('getSingle')->willReturnOnConsecutiveCalls(
            ['uid' => 123],
            null
        );

        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)
            ->onlyMethods(['createBackendLayoutInstance'])
            ->setConstructorArgs([$this->providerResolver, $this->recordService])
            ->getMock();
        $instance->method('createBackendLayoutInstance')
            ->willReturn(
                $this->getMockBuilder(BackendLayout::class)
                    ->setMethods(['dummy'])
                    ->disableOriginalConstructor()
                    ->getMock()
            );
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
    }

    /**
     * @return void
     */
    public function testGetBackendLayoutReturnsEmptyLayoutWithoutProvider()
    {
        $this->recordService->method('getSingle')->willReturn(['uid' => 123]);
        $this->providerResolver->method('resolvePageProvider')->willReturn(null);

        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)
            ->onlyMethods(['createBackendLayoutInstance'])
            ->setConstructorArgs([$this->providerResolver, $this->recordService])
            ->getMock();
        $instance->method('createBackendLayoutInstance')
            ->willReturn($this->createBackendLayoutMock());
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
    }

    /**
     * @return void
     */
    public function testGetBackendLayoutReturnsBackendLayoutWithRecordAndProvider()
    {
        $backendLayout = $this->getMockBuilder(BackendLayout::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $grid = $this->getMockBuilder(Grid::class)
            ->setMethods(['buildBackendLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $grid->method('buildBackendLayout')->willReturn($backendLayout);

        $provider = $this->getMockBuilder(PageProvider::class)
            ->onlyMethods(['getGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider->method('getGrid')->willReturn($grid);

        $this->recordService->method('getSingle')->willReturn(['uid' => 123]);
        $this->providerResolver->method('resolvePageProvider')->willReturn($provider);

        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)
            ->onlyMethods(['createBackendLayoutInstance'])
            ->setConstructorArgs([$this->providerResolver, $this->recordService])
            ->getMock();
        $instance->method('createBackendLayoutInstance')
            ->willReturn($this->createBackendLayoutMock());
        $result = $instance->getBackendLayout('grid', 1);
        $this->assertInstanceOf(BackendLayout::class, $result);
    }

    /**
     * @return void
     */
    public function testAddBackendLayouts()
    {
        $instance = $this->getMockBuilder(BackendLayoutDataProvider::class)
            ->onlyMethods(['createBackendLayoutInstance'])
            ->setConstructorArgs([$this->providerResolver, $this->recordService])
            ->getMock();
        $instance->method('createBackendLayoutInstance')
            ->willReturn($this->createBackendLayoutMock());
        $collection = new BackendLayoutCollection('collection');
        $context = new DataProviderContext();
        $context->setPageId(1);
        $instance->addBackendLayouts($context, $collection);
        $all = $collection->getAll();
        $this->assertInstanceOf(BackendLayout::class, reset($all));
    }

    protected function createBackendLayoutMock(): BackendLayout
    {
        $mock = $this->getMockBuilder(BackendLayout::class)
            ->onlyMethods(['getIdentifier'])
            ->disableOriginalConstructor()
            ->getMock();
        $mock->method('getIdentifier')->willReturn('belayout');
        return $mock;
    }
}
