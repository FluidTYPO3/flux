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

/**
 * Class PageLayoutDataProviderTest
 */
class PageLayoutDataProviderTest extends AbstractTestCase
{
    /**
     * @param array $parameters
     * @param array $items
     * @param array $expected
     * @test
     * @dataProvider getAddItemsTestValues
     */
    public function testAddItems(array $parameters, array $items, array $expected)
    {
        $this->markTestSkipped();
        $parameters['items'] = &$items;
        $instance = $this->getMockBuilder(PageLayoutDataProvider::class)->disableOriginalConstructor()->getMock();
        $form = Form::create();
        $pageService = $this->getMockBuilder(PageService::class)->setMethods(['getAvailablePageTemplateFiles'])->getMock();
        $pageService->expects($this->once())->method('getAvailablePageTemplateFiles')->willReturn(['flux' => ['foo.html' => $form]]);
        $instance->injectPageService($pageService);
        $instance->addItems($parameters);
        $this->assertSame($expected, $items);
    }

    /**
     * @return array
     */
    public function getAddItemsTestValues()
    {
        return [

            [
                [],
                [],
                [['Flux: Fluid Integration', '--div--'], [null, '->', null]]
            ],
            [
                [],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['Flux: Fluid Integration', '--div--'], [null, '->', null]]
            ],
            [
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default', '', 'actions-move-down'], ['Flux: Fluid Integration', '--div--'], [null, '->', null]]
            ],
            [
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1, 'is_siteroot' => false]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default', '', 'actions-move-down'], ['Flux: Fluid Integration', '--div--'], [null, '->', null]]
            ],
            [
                ['field' => 'tx_fed_page_controller_action', 'row' => ['pid' => 0, 'is_siteroot' => true]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['Flux: Fluid Integration', '--div--'], [null, '->', null]]
            ],

        ];
    }
}
