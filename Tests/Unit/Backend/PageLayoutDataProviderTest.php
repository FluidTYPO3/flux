<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\PageLayoutDataProvider;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class PageLayoutDataProviderTest extends AbstractTestCase
{
    private ConfigurationManagerInterface $configurationManager;
    private PageService $pageService;
    private SiteFinder $siteFinder;

    protected function setUp(): void
    {
        $this->pageService = $this->getMockBuilder(PageService::class)
            ->onlyMethods(['getAvailablePageTemplateFiles'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)
            ->getMockForAbstractClass();
        $this->configurationManager->method('getConfiguration')->willReturn([]);

        $this->siteFinder = $this->getMockBuilder(SiteFinder::class)
            ->onlyMethods(['getSiteByPageId'])
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    protected function getConstructorArguments(): array
    {
        return [
            $this->configurationManager,
            $this->pageService,
            $this->siteFinder,
        ];
    }

    /**
     * @param Form $form
     * @param array $parameters
     * @param array $items
     * @param array $expected
     * @test
     * @dataProvider getAddItemsTestValues
     */
    public function testAddItems(Form $form, array $parameters, array $items, array $expected)
    {
        $this->pageService->expects($this->once())
            ->method('getAvailablePageTemplateFiles')
            ->willReturn(['flux' => [$form]]);

        $parameters['items'] = &$items;
        $instance = $this->getMockBuilder(PageLayoutDataProvider::class)
            ->onlyMethods(['isExtensionLoaded'])
            ->setConstructorArgs($this->getConstructorArguments())
            ->getMock();
        $instance->method('isExtensionLoaded')->willReturn(false);

        $instance->addItems($parameters);
        $this->assertSame($expected, $items);
    }

    /**
     * @return array
     */
    public function getAddItemsTestValues()
    {
        $label = 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default';

        $formWithoutTemplateFile = $form = $this->getMockBuilder(Form::class)
            ->onlyMethods(['getOption'])
            ->getMock();
        $formWithTemplateFile = clone $formWithoutTemplateFile;
        $formWithTemplateFile->method('getOption')->willReturn('Tests/Fixtures/Templates/Page/Dummy.html');

        return [
            [
                $formWithoutTemplateFile,
                [],
                [],
                [['Flux', '--div--'], []]
            ],
            [
                $formWithoutTemplateFile,
                [],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['Flux', '--div--'], []]
            ],
            [
                $formWithoutTemplateFile,
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1]],
                [['foo', 'bar', 'baz']],
                [
                    ['foo', 'bar', 'baz'],
                    [$label, '', 'actions-move-down'],
                    ['Flux', '--div--'],
                    []
                ]
            ],
            [
                $formWithTemplateFile,
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1, 'is_siteroot' => false]],
                [['foo', 'bar', 'baz']],
                [
                    ['foo', 'bar', 'baz'],
                    [$label, '', 'actions-move-down'],
                    ['Flux', '--div--'],
                    [
                        'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.',
                        'FluidTYPO3.Flux->tests/Fixtures/Templates/Page/Dummy.html',
                        null
                    ]
                ]
            ],
            [
                $formWithTemplateFile,
                ['field' => 'tx_fed_page_controller_action', 'row' => ['pid' => 0, 'is_siteroot' => true]],
                [['foo', 'bar', 'baz']],
                [
                    ['foo', 'bar', 'baz'],
                    ['Flux', '--div--'],
                    [
                        'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.',
                        'FluidTYPO3.Flux->tests/Fixtures/Templates/Page/Dummy.html',
                        null
                    ]
                ]
            ],
        ];
    }
}
