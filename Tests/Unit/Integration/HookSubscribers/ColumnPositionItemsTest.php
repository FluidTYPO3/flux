<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Integration\FormEngine\SelectOption;
use FluidTYPO3\Flux\Integration\HookSubscribers\ColumnPositionItems;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ColumnPositionItemsTest extends AbstractTestCase
{
    private WorkspacesAwareRecordService $recordService;
    private ProviderResolver $providerResolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDoesNotProcessWithoutParentRecord(): void
    {
        $this->recordService->method('getSingle')->willReturn(null);
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(
            $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass()
        );
        $subject = new ColumnPositionItems($this->recordService, $this->providerResolver);

        $parameters = ['row' => ['colPos' => 101]];
        $subject->colPosListItemProcFunc($parameters);
        self::assertSame($parameters, $parameters);
    }

    public function testDoesNotProcessWithoutProvider(): void
    {
        $this->recordService->method('getSingle')->willReturn(['uid' => 123]);
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(null);
        $subject = new ColumnPositionItems($this->recordService, $this->providerResolver);

        $parameters = ['row' => ['colPos' => 101]];
        $subject->colPosListItemProcFunc($parameters);
        self::assertSame($parameters, $parameters);
    }

    public function testDoesNotProcessWithoutProviderAndParentRecord(): void
    {
        $this->recordService->method('getSingle')->willReturn(null);
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn(null);
        $subject = new ColumnPositionItems($this->recordService, $this->providerResolver);

        $parameters = ['row' => ['colPos' => 101]];
        $subject->colPosListItemProcFunc($parameters);
        self::assertSame($parameters, $parameters);
    }

    public function testAddsExpectedItems(): void
    {
        $grid = Grid::create();
        $grid->createContainer(Row::class, 'row')->createContainer(Column::class, 'col')->setColumnPosition(3);

        $provider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $this->recordService->method('getSingle')->willReturn(['uid' => 123]);
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $subject = new ColumnPositionItems($this->recordService, $this->providerResolver);

        $parameters = ['row' => ['colPos' => 103]];
        $expected = $parameters;
        $expected['items'] = [
            (new SelectOption(
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.backendLayout.columnsInParent',
                '--div--'
            ))->toArray(),
            (new SelectOption(
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux..columns.col',
                103
            ))->toArray(),
        ];

        $subject->colPosListItemProcFunc($parameters);
        self::assertSame($expected, $parameters);
    }
}
