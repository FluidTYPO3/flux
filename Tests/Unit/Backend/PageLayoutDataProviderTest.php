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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
        $parameters['items'] = &$items;
        $instance = $this->getMockBuilder(PageLayoutDataProvider::class)->setMethods(['isExtensionLoaded'])->disableOriginalConstructor()->getMock();
        $instance->method('isExtensionLoaded')->willReturn(false);
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        $configurationManager = $this->getMockBuilder(ConfigurationManagerInterface::class)->getMockForAbstractClass();
        $configurationManager->method('getConfiguration')->willReturn([]);
        $pageService = $this->getMockBuilder(PageService::class)->setMethods(['getAvailablePageTemplateFiles'])->getMock();
        $pageService->expects($this->once())->method('getAvailablePageTemplateFiles')->willReturn(['flux' => [$form]]);
        $instance->injectPageService($pageService);
        $instance->injectConfigurationManager($configurationManager);
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
                [['Flux', '--div--'], []]
            ],
            [
                [],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['Flux', '--div--'], []]
            ],
            [
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default', '', 'actions-move-down'], ['Flux', '--div--'], []]
            ],
            [
                ['field' => 'tx_fed_page_controller_action_sub', 'row' => ['pid' => 1, 'is_siteroot' => false]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['LLL:EXT:flux/Resources/Private/Language/locallang.xlf:pages.tx_fed_page_controller_action.default', '', 'actions-move-down'], ['Flux', '--div--'], []]
            ],
            [
                ['field' => 'tx_fed_page_controller_action', 'row' => ['pid' => 0, 'is_siteroot' => true]],
                [['foo', 'bar', 'baz']],
                [['foo', 'bar', 'baz'], ['Flux', '--div--'], []]
            ],

        ];
    }
}
