<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;

class WizardItemsTest extends AbstractTestCase
{

    /**
     * @dataProvider getTestElementsWhiteAndBlackListsAndExpectedList
     * @test
     * @param array $items
     * @param string $whitelist
     * @param string $blacklist
     * @param array $expectedList
     */
    public function processesWizardItems($items, $whitelist, $blacklist, $expectedList)
    {
        $instance = $this->getMockBuilder(WizardItems::class)
            ->setMethods(['dummy'])
            ->getMock();
        $emulatedPageAndContentRecord = ['uid' => 1, 'tx_flux_column' => 'area'];

        $controller = $this->getMockBuilder(NewContentElementController::class)
            ->setMethods(['init'])
            ->disableOriginalConstructor()
            ->getMock();

        $grid = new Grid();
        $row = new Row();

        $column = new Column();
        $column->setColumnPosition(0);
        $column->setName('area');
        $column->setVariable('allowedContentTypes', $whitelist);
        $column->setVariable('deniedContentTypes', $blacklist);

        $row->add($column);
        $grid->add($row);

        $provider1 = $this->objectManager->get(Provider::class);
        $provider1->setTemplatePaths([]);
        $provider1->setTemplateVariables([]);
        $provider1->setGrid($grid);
        $provider1->setForm(Form::create());

        $provider2 = $this->getMockBuilder(Provider::class)->setMethods(['getGrid'])->getMock();
        $provider2->expects($this->once())->method('getGrid')->will($this->returnValue(null));

        $configurationService = $this->getMockBuilder(FluxService::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->getMock();

        $configurationService->expects($this->once())->method('resolveConfigurationProviders')
            ->will($this->returnValue([$provider1, $provider2]));

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->getMock();

        $recordService->expects($this->once())->method('getSingle')->will($this->returnValue($emulatedPageAndContentRecord));
        $instance->injectConfigurationService($configurationService);
        $instance->injectRecordService($recordService);
        $instance->manipulateWizardItems($items, $controller);

        $this->assertEquals($expectedList, $items);
    }

    /**
     * @return array
     */
    public function getTestElementsWhiteAndBlackListsAndExpectedList()
    {
        $items = [
            'plugins' => ['title' => 'Nice header'],
            'plugins_test1' => ['tt_content_defValues' => ['CType' => 'test1']],
            'plugins_test2' => ['tt_content_defValues' => ['CType' => 'test2']]
        ];
        return [
            [
                $items,
                null,
                null,
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test1' => ['tt_content_defValues' => ['CType' => 'test1']],
                    'plugins_test2' => ['tt_content_defValues' => ['CType' => 'test2']]
                ],
            ],
            [
                $items,
                'test1',
                null,
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test1' => ['tt_content_defValues' => ['CType' => 'test1']]
                ],
            ],
            [
                $items,
                null,
                'test1',
                [
                    'plugins' => ['title' => 'Nice header'],
                    'plugins_test2' => ['tt_content_defValues' => ['CType' => 'test2']]
                ],
            ],
            [
                $items,
                'test1',
                'test1',
                [],
            ],
        ];
    }

    /**
     * @test
     */
    public function testManipulateWizardItemsWithDefaultValues()
    {
        $items = [
            ['tt_content_defValues' => [], 'params' => '']
        ];
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(
                [
                    'getWhiteAndBlackListsFromPageAndContentColumn',
                    'applyWhitelist', 'applyBlacklist', 'trimItems'
                ]
            )
            ->getMock();

        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder(DatabaseConnection::class)
            ->setMethods(['exec_SELECTgetSingleRow'])
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetSingleRow')->willReturn(null);
        $lists = [[], []];
        $instance->expects($this->once())->method('getWhiteAndBlackListsFromPageAndContentColumn')->will($this->returnValue($lists));
        $instance->expects($this->once())->method('applyWhitelist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('applyBlacklist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('trimItems')->will($this->returnValue($items));
        $controller = $this->getMockBuilder(NewContentElementController::class)->setMethods(['init'])->disableOriginalConstructor()->getMock();
        $instance->manipulateWizardItems($items, $controller);
        $this->assertNotEmpty($items);
    }
}
