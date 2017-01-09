<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Core\Database\DatabaseConnection;

class WizardItemsHookSubscriberTest extends AbstractTestCase
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
        $instance = $this->getMockBuilder(WizardItemsHookSubscriber::class)
            ->setMethods(['getAreaNameAndParentFromRelativeRecordOrDefaults'])
            ->getMock();
        $instance->expects($this->once())->method('getAreaNameAndParentFromRelativeRecordOrDefaults')
            ->willReturn([1, 'area']);
        $emulatedPageAndContentRecord = ['uid' => 1, 'tx_flux_column' => 'area'];

        $controller = $this->getMockBuilder(NewContentElementController::class)
            ->setMethods(['init'])
            ->disableOriginalConstructor()
            ->getMock();
        $controller->colPos = 0;
        $controller->uid_pid = -1;

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

        $provider2 = $this->getMockBuilder(Provider::class)->setMethods(['getGrid'])->getMock();
        $provider2->expects($this->exactly(2))->method('getGrid')->will($this->returnValue(null));

        $configurationService = $this->getMockBuilder(FluxService::class)
            ->setMethods(['resolveConfigurationProviders'])
            ->getMock();

        $configurationService->expects($this->exactly(2))->method('resolveConfigurationProviders')
            ->will($this->returnValue([$provider1, $provider2]));

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)
            ->setMethods(['getSingle'])
            ->getMock();

        $recordService->expects($this->exactly(2))->method('getSingle')->will($this->returnValue($emulatedPageAndContentRecord));
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
    public function applyDefaultValuesAppliesValues()
    {
        $instance = new WizardItemsHookSubscriber();
        $defaultValues = ['tx_flux_column' => 'foobararea', 'tx_flux_parent' => 321];
        $items = [
            ['tt_content_defValues' => '', 'params' => '']
        ];
        $result = $this->callInaccessibleMethod($instance, 'applyDefaultValues', $items, $defaultValues);
        $this->assertEquals($defaultValues['tx_flux_column'], $result[0]['tt_content_defValues']['tx_flux_column']);
        $this->assertEquals($defaultValues['tx_flux_parent'], $result[0]['tt_content_defValues']['tx_flux_parent']);
        $this->assertContains('[tx_flux_column]=foobararea', $result[0]['params']);
        $this->assertContains('[tx_flux_parent]=321', $result[0]['params']);
    }

    /**
     * @test
     */
    public function testManipulateWizardItemsWithDefaultValues()
    {
        $defaultValues = ['tx_flux_column' => 'foobararea', 'tx_flux_parent' => 321];
        $items = [
            ['tt_content_defValues' => '', 'params' => '']
        ];
        $instance = $this->getMockBuilder($this->createInstanceClassName())
            ->setMethods(
                [
                    'getDefaultValues', 'getWhiteAndBlackListsFromPageAndContentColumn',
                    'applyDefaultValues', 'applyWhitelist', 'applyBlacklist', 'trimItems'
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
        $instance->expects($this->once())->method('applyDefaultValues')->will($this->returnValue($items));
        $instance->expects($this->once())->method('applyWhitelist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('applyBlacklist')->will($this->returnValue($items));
        $instance->expects($this->once())->method('trimItems')->will($this->returnValue($items));
        $instance->expects($this->once())->method('getDefaultValues')->will($this->returnValue($defaultValues));
        $controller = $this->getMockBuilder(NewContentElementController::class)->setMethods(['init'])->disableOriginalConstructor()->getMock();
        $instance->manipulateWizardItems($items, $controller);
        $this->assertNotEmpty($items);
    }

    /**
     * @dataProvider getAreaNameAndParentFromRelativeRecordOrDefaults
     * @param integer $relativeUid
     * @param array $expected
     */
    public function testGetAreaNameAndParentFromRelativeRecordOrDefaults($relativeUid, array $expected)
    {
        $defaults = ['tx_flux_column' => 'defaultarea', 'tx_flux_parent' => 999];
        $inRecord = ['tx_flux_column' => 'recordarea', 'tx_flux_parent' => 111];

        $recordService = $this->getMockBuilder(WorkspacesAwareRecordService::class)->setMethods(['getSingle'])->getMock();
        $recordService->expects($this->any())->method('getSingle')->willReturn($inRecord);

        $instance = $this->getMockBuilder(WizardItemsHookSubscriber::class)->setMethods(['getDefaultValues'])->getMock();
        $instance->expects($this->once())->method('getDefaultValues')->willReturn($defaults);
        $instance->injectRecordService($recordService);

        $result = $this->callInaccessibleMethod($instance, 'getAreaNameAndParentFromRelativeRecordOrDefaults', $relativeUid);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getAreaNameAndParentFromRelativeRecordOrDefaults()
    {
        return [
            [0, [999, 'defaultarea']],
            [1, [999, 'defaultarea']],
            [-1, [111, 'recordarea']]
        ];
    }
}
