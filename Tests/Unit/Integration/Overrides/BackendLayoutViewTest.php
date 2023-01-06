<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Backend\View\BackendLayout\DataProviderCollection;
use TYPO3\CMS\Core\Localization\LanguageService;

class BackendLayoutViewTest extends AbstractTestCase
{
    private ?FluxService $fluxService;

    protected function setUp(): void
    {
        $this->fluxService = $this->getMockBuilder(FluxService::class)
            ->setMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->singletonInstances[FluxService::class] = $this->fluxService;

        parent::setUp();
    }

    public function testCanSetProvider()
    {
        $instance = $this->getMockBuilder(BackendLayoutView::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $instance->setProvider($provider);
        $this->assertSame($provider, $this->getInaccessiblePropertyValue($instance, 'provider'));
    }

    public function testCanSetRecord()
    {
        $instance = $this->getMockBuilder(BackendLayoutView::class)->setMethods(['dummy'])->disableOriginalConstructor()->getMock();
        $record = ['foo' => 'bar'];
        $instance->setRecord($record);
        $this->assertSame($record, $this->getInaccessiblePropertyValue($instance, 'record'));
    }

    public function testColPosListItemProcFuncWithoutSelectedIdentifier(): void
    {
        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(['getSelectedCombinedIdentifier', 'determinePageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::once())->method('determinePageId')->willReturn(1);
        $subject->expects(self::once())->method('getSelectedCombinedIdentifier')->willReturn(false);

        $parameters = ['row' => ['uid' => 123, 'colPos' => 1], 'table' => 'tt_content', 'items' => []];

        $subject->colPosListItemProcFunc($parameters);
    }

    public function testColPosListItemProcFuncWithForeignSelectedIdentifier(): void
    {
        $providerCollection = $this->getMockBuilder(DataProviderCollection::class)
            ->setMethods(['getBackendLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerCollection->method('getBackendLayout')->willReturn(null);

        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'getBackendLayoutForPage',
                    'getSelectedCombinedIdentifier',
                    'determinePageId',
                    'loadRecordFromTable',
                    'getDataProviderCollection',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getSelectedCombinedIdentifier')->willReturn('foreign__foobar');
        $subject->method('determinePageId')->willReturn(0);
        $subject->method('getDataProviderCollection')->willReturn($providerCollection);
        $subject->expects(self::never())->method('loadRecordFromTable');
        $subject->method('getBackendLayoutForPage')->willReturn(
            $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock()
        );

        $parameters = ['row' => ['uid' => 123, 'colPos' => 1], 'table' => 'tt_content', 'items' => []];

        $subject->colPosListItemProcFunc($parameters);
    }

    public function testColPosListItemProcFuncWithoutPageRecord(): void
    {
        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'getBackendLayoutForPage',
                    'getSelectedCombinedIdentifier',
                    'determinePageId',
                    'loadRecordFromTable',
                    'resolvePrimaryProviderForRecord',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getSelectedCombinedIdentifier')->willReturn('flux__foobar');
        $subject->method('determinePageId')->willReturn(0);
        $subject->method('loadRecordFromTable')->willReturn(null);
        $subject->method('getBackendLayoutForPage')->willReturn(
            $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock()
        );
        $subject->expects(self::never())->method('resolvePrimaryProviderForRecord');

        $parameters = ['row' => ['uid' => 123, 'colPos' => 1], 'table' => 'tt_content', 'items' => []];

        $subject->colPosListItemProcFunc($parameters);
    }

    public function testColPosListItemProcFuncWithPageLevelProvider(): void
    {
        $grid = $this->getMockBuilder(Grid::class)
            ->setMethods(['buildExtendedBackendLayoutArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $grid->expects(self::once())->method('buildExtendedBackendLayoutArray')->with(0);

        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'getBackendLayoutForPage',
                    'getSelectedCombinedIdentifier',
                    'determinePageId',
                    'loadRecordFromTable',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getSelectedCombinedIdentifier')->willReturn('flux__foobar');
        $subject->method('determinePageId')->willReturn(0);
        $subject->method('loadRecordFromTable')->willReturn(['uid' => 123]);
        $subject->method('getBackendLayoutForPage')->willReturn(
            $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock()
        );

        $parameters = ['row' => ['uid' => 123, 'colPos' => 1], 'table' => 'tt_content', 'items' => []];

        $subject->colPosListItemProcFunc($parameters);
    }

    /**
     * @param int|array $uid
     * @return void
     * @dataProvider getColPosListItemProcFuncWithDelegateProviderTestValues
     */
    public function testColPosListItemProcFuncWithDelegateProvider($uid): void
    {
        $grid = $this->getMockBuilder(Grid::class)
            ->setMethods(['buildExtendedBackendLayoutArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $grid->expects(self::once())->method('buildExtendedBackendLayoutArray')->with(1);

        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'getBackendLayoutForPage',
                    'getSelectedCombinedIdentifier',
                    'determinePageId',
                    'loadRecordFromTable',
                    'resolvePrimaryProviderForRecord',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('getSelectedCombinedIdentifier')->willReturn('flux__foobar');
        $subject->method('determinePageId')->willReturn(0);
        $subject->method('loadRecordFromTable')->willReturn(['uid' => 123]);
        $subject->method('getBackendLayoutForPage')->willReturn(
            $this->getMockBuilder(BackendLayout::class)->disableOriginalConstructor()->getMock()
        );
        $subject->method('resolvePrimaryProviderForRecord')->willReturn(null);
        $subject->setProvider($provider);

        $parameters = [
            'row' => ['uid' => 123, 'colPos' => 1, 'l18n_parent' => $uid],
            'table' => 'tt_content',
            'items' => []
        ];

        $subject->colPosListItemProcFunc($parameters);
    }

    public function getColPosListItemProcFuncWithDelegateProviderTestValues(): array
    {
        return [
            'uid as int' => [1],
            'uid as array with int' => [[1]],
        ];
    }

    public function testColPosListItemProcFuncWithoutProviders(): void
    {
        $providerCollection = $this->getMockBuilder(DataProviderCollection::class)
            ->setMethods(['getBackendLayout'])
            ->disableOriginalConstructor()
            ->getMock();
        $providerCollection->method('getBackendLayout')->willReturn(null);

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn(null);

        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'getBackendLayoutForPage',
                    'getSelectedCombinedIdentifier',
                    'determinePageId',
                    'loadRecordFromTable',
                    'getDataProviderCollection',
                    'resolvePrimaryProviderForRecord',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->expects(self::atLeastOnce())->method('getSelectedCombinedIdentifier')->willReturn('flux__foobar');
        $subject->method('determinePageId')->willReturn(0);
        $subject->method('loadRecordFromTable')->willReturn(['uid' => 123]);
        $subject->method('getDataProviderCollection')->willReturn($providerCollection);
        $subject->method('getBackendLayoutForPage')->willReturn(null);

        $parameters = [
            'row' => ['uid' => 123, 'colPos' => 1, 'l18n_parent' => 1],
            'table' => 'tt_content',
            'items' => []
        ];

        $subject->colPosListItemProcFunc($parameters);
    }

    public function testAddColPosListLayoutItemsWithPageRecord(): void
    {
        $grid = $this->getMockBuilder(Grid::class)
            ->setMethods(['buildExtendedBackendLayoutArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $grid->expects(self::once())
            ->method('buildExtendedBackendLayoutArray')
            ->with(123)
            ->willReturn(['__items' => []]);

        $provider = $this->getMockBuilder(GridProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getGrid')->willReturn($grid);

        $this->fluxService->method('resolvePrimaryConfigurationProvider')->willReturn($provider);

        $languageService = $this->getMockBuilder(LanguageService::class)
            ->setMethods(['sL'])
            ->disableOriginalConstructor()
            ->getMock();
        $languageService->method('sL')->willReturn('Label');

        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'determinePageId',
                    'getSelectedBackendLayout',
                    'loadRecordFromTable',
                    'getLanguageService',
                    'resolvePrimaryProviderForRecord',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('determinePageId')->willReturn(123);
        $subject->method('loadRecordFromTable')->willReturn(['uid' => 123]);
        $subject->method('getSelectedBackendLayout')->willReturn(['__items' => []]);
        $subject->method('getLanguageService')->willReturn($languageService);
        $subject->method('resolvePrimaryProviderForRecord')->willReturn($provider);

        $subject->setRecord(['uid' => 123, 'colPos' => 12300]);

        $this->setInaccessiblePropertyValue($subject, 'addingItemsForContent', true);

        $output = $this->callInaccessibleMethod($subject, 'addColPosListLayoutItems', 123, []);
        self::assertSame([['Label', '--div--']], $output);
    }

    public function testAddColPosListLayoutItemsWithoutPageRecord(): void
    {
        $subject = $this->getMockBuilder(BackendLayoutView::class)
            ->setMethods(
                [
                    'determinePageId',
                    'getSelectedBackendLayout',
                    'loadRecordFromTable',
                    'getLanguageService',
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('determinePageId')->willReturn(123);
        $subject->method('loadRecordFromTable')->willReturn(null);
        $subject->method('getSelectedBackendLayout')->willReturn(['__items' => []]);

        $subject->setRecord(['uid' => 123, 'colPos' => 12300]);

        $this->setInaccessiblePropertyValue($subject, 'addingItemsForContent', true);

        $output = $this->callInaccessibleMethod($subject, 'addColPosListLayoutItems', 123, []);
        self::assertSame([], $output);
    }
}
