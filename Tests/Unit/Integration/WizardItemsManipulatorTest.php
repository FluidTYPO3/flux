<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Integration\WizardItemsManipulator;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

class WizardItemsManipulatorTest extends AbstractTestCase
{
    private ProviderResolver $providerResolver;
    private WorkspacesAwareRecordService $recordService;
    private SiteFinder $siteFinder;
    private Site $site;
    private ContentTypeManager $contentTypeManager;
    private WizardItemsManipulator $subject;

    protected function setUp(): void
    {
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->onlyMethods(['resolveConfigurationProviders'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->onlyMethods(['getSingle'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contentTypeManager = $this->getMockBuilder(ContentTypeManager::class)
            ->onlyMethods(['fetchContentTypeNames'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->siteFinder = $this->getMockBuilder(SiteFinder::class)
            ->onlyMethods(['getSiteByPageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->site = $this->getMockBuilder(Site::class)
            ->onlyMethods(['getConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = new WizardItemsManipulator(...$this->getConstructorArguments());

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->providerResolver,
            $this->recordService,
            $this->contentTypeManager,
            $this->siteFinder,
        ];
    }

    /**
     * @dataProvider getTestElementsWhiteAndBlackListsAndExpectedList
     * @test
     */
    public function processesWizardItems(
        array $items,
        ?string $whitelist,
        ?string $blacklist,
        array $expectedList
    ): void {
        $emulatedPageAndContentRecord = ['uid' => 1, 'colPos' => 100];

        $grid = new Grid();
        $row = new Row();

        $column = new Column();
        $column->setColumnPosition(0);
        $column->setName('area');
        $column->setVariable('allowedContentTypes', $whitelist);
        $column->setVariable('deniedContentTypes', $blacklist);

        $row->add($column);
        $grid->add($row);

        $provider1 = $this->getMockBuilder(Provider::class)
            ->addMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider1->setTemplatePaths([]);
        $provider1->setTemplateVariables([]);
        $provider1->setGrid($grid);
        $provider1->setForm($this->getMockBuilder(Form::class)->getMock());

        $provider2 = $this->getMockBuilder(Provider::class)
            ->onlyMethods(['getGrid'])
            ->disableOriginalConstructor()
            ->getMock();
        $provider2->expects($this->once())->method('getGrid')->will($this->returnValue(Grid::create()));

        $this->providerResolver->expects($this->once())
            ->method('resolveConfigurationProviders')
            ->will($this->returnValue([$provider1, $provider2]));

        $this->recordService->expects($this->once())
            ->method('getSingle')
            ->willReturn($emulatedPageAndContentRecord);

        $items = $this->subject->manipulateWizardItems($items, 1, 0);

        $this->assertEquals($expectedList, $items);
    }

    public function getTestElementsWhiteAndBlackListsAndExpectedList(): array
    {
        $items = [
            'plugins' => ['title' => 'Nice header'],
            'plugins_test1' => ['tt_content_defValues' => ['CType' => 'test1']],
            'plugins_test2' => ['tt_content_defValues' => ['CType' => 'test2']]
        ];
        return [
            'no witelist or blacklist' => [
                $items,
                null,
                null,
                $items,
            ],
            'if whitelist is specified, includes only whitelisted item(s)' => [
                $items,
                'test1',
                null,
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test1' => ['tt_content_defValues' => ['CType' => 'test1']]
                ],
            ],
            'if blackist is specified, removes blacklisted item(s)' => [
                $items,
                null,
                'test1',
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test2' => ['tt_content_defValues' => ['CType' => 'test2']]
                ],
            ],
            'blacklisting a whitelisted item removes the item' => [
                $items,
                'test1',
                'test1',
                [],
            ],
        ];
    }

    public function testManipulateWizardItemsWithDefaultValues()
    {
        $items = [
            ['tt_content_defValues' => [], 'params' => '']
        ];
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->onlyMethods(
                [
                    'getWhiteAndBlackListsFromPageAndContentColumn',
                    'applyWhitelist', 'applyBlacklist', 'trimItems'
                ]
            )
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();

        $lists = [[], []];
        $instance->expects($this->once())
            ->method('getWhiteAndBlackListsFromPageAndContentColumn')
            ->will($this->returnValue($lists));
        $instance->expects($this->once())->method('applyWhitelist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('applyBlacklist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('trimItems')->will($this->returnValue($items));
        $instance->manipulateWizardItems($items, 1, 12);
        $this->assertNotEmpty($items);
    }

    public function testManipulateWizardItemsFiltersAllowedContentTypes(): void
    {
        $this->contentTypeManager->method('fetchContentTypeNames')->willReturn(['type1', 'type2']);
        $this->siteFinder->method('getSiteByPageId')->willReturn($this->site);
        $this->site->method('getConfiguration')->willReturn(['flux_content_types' => 'type1']);

        $type1 = ['tt_content_defValues' => ['CType' => 'type1'], 'params' => ''];
        $type2 = ['tt_content_defValues' => ['CType' => 'type2'], 'params' => ''];
        $items = [$type1, $type2];

        $subject = $this->getMockBuilder(WizardItemsManipulator::class)
            ->onlyMethods(['filterPermittedFluidContentTypesByInsertionPosition'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $subject->method('filterPermittedFluidContentTypesByInsertionPosition')->willReturnArgument(0);

        $items = $subject->manipulateWizardItems($items, 1, 12);
        self::assertSame([$type1], $items);
    }

    public function testGetWhiteAndBlackListsFromPageAndContentColumn(): void
    {
        $pageUid = 123;
        $parentRecordUid = 456;
        $columnPosition = ColumnNumberUtility::calculateColumnNumberForParentAndColumn($parentRecordUid, 13);

        $pageRecord = ['uid' => 123];
        $contentRecord = ['uid' => 456, 'colPos' => 3];

        $pageGrid = Grid::create();
        $pageGrid->createContainer(Row::class, 'row')
            ->createContainer(Column::class, 'column')
            ->setColumnPosition(3)
            ->setVariable('allowedContentTypes', 'c1,c2')
            ->setVariable('deniedContentTypes', 'c8');

        $contentGrid = Grid::create();
        $contentGrid->createContainer(Row::class, 'row')
            ->createContainer(Column::class, 'column')
            ->setColumnPosition(13)
            ->setVariable('allowedContentTypes', 'c4,c5')
            ->setVariable('deniedContentTypes', 'c9');

        $pageProvider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $pageProvider->method('getGrid')->willReturn($pageGrid);

        $contentProvider = $this->getMockBuilder(ProviderInterface::class)->getMockForAbstractClass();
        $contentProvider->method('getGrid')->willReturn($contentGrid);

        $this->recordService->method('getSingle')->willReturnMap(
            [
                ['pages', '*', $pageUid, $pageRecord],
                ['tt_content', 'uid,colPos', $parentRecordUid, ['uid' => 789, 'colPos' => 3]],
                ['tt_content', '*', $parentRecordUid, $contentRecord],
            ]
        );
        $this->providerResolver->method('resolveConfigurationProviders')->willReturnOnConsecutiveCalls(
            [$pageProvider],
            [$contentProvider]
        );

        [$whitelist, $blacklist] = $this->callInaccessibleMethod(
            $this->subject,
            'getWhiteAndBlackListsFromPageAndContentColumn',
            $pageUid,
            $columnPosition
        );

        $expectedWhitelist = ['c4', 'c5'];
        $expectedBlacklist = ['c8', 'c9'];

        self::assertSame($expectedWhitelist, $whitelist, 'Whitelist does not match expected value');
        self::assertSame($expectedBlacklist, $blacklist, 'Blacklist does not match expected value');
    }

    public function testManipulateWizardItemsToleratesSiteNotFoundException(): void
    {
        $this->siteFinder->expects(self::atLeastOnce())
            ->method('getSiteByPageId')
            ->willThrowException(new SiteNotFoundException('test'));
        $this->subject->manipulateWizardItems([], 1, null);
    }

    public function testFindParentColumnPositionRecursesToSelfWithVirtualColumnPosition(): void
    {
        $this->recordService->method('getSingle')->willReturnOnConsecutiveCalls(
            ['colPos' => 10010],
            ['colPos' => 4]
        );
        $output = $this->callInaccessibleMethod($this->subject, 'findParentColumnPosition', 1);
        self::assertSame(4, $output);
    }
}
