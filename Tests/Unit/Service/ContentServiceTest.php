<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ContentServiceTest
 */
class ContentServiceTest extends AbstractTestCase
{

    /**
     * @return ContentService
     */
    protected function createInstance()
    {
        $class = substr(get_class($this), 0, -4);
        $class = str_replace('Tests\\Unit\\', '', $class);
        $instance = $this->objectManager->get($class);
        return $instance;
    }

    /**
     * @test
     */
    public function affectByRequestParametersAppliesParent()
    {
        $parameters['overrideVals']['tt_content']['tx_flux_parent'] = 999999;
        $record = Records::$contentRecordIsParentAndHasChildren;
        $this->assertSame(0, $record['tx_flux_parent']);
        $this->assertSame(0, $record['colPos']);
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $this->createInstance()->affectRecordByRequestParameters('NEW12345', $record, $parameters, $tceMain);
        $this->assertSame(999999, $record['tx_flux_parent']);
        $this->assertSame(ContentService::COLPOS_FLUXCONTENT, $record['colPos']);
    }

    /**
     * @test
     */
    public function canInitializeBlankRecord()
    {
        $methods = array('loadRecordsFromDatabase', 'loadRecordFromDatabase', 'updateRecordInDataMap');
        $mock = $this->createMock($methods);
        $row = array('uid' => -1);
        $tceMain = $this->getMockBuilder('TYPO3\CMS\Core\DataHandling\DataHandler')->getMock();
        $tceMain->substNEWwithIDs = array('NEW12345' => -1);
        $result = $mock->initializeRecord('NEW12345', $row, $tceMain);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function moveRecordWithNegativeRelativeToLoadsRelativeCopiesValuesSetsColumnPositionAndUpdatesRelativeToValue()
    {
        $methods = array('loadRecordFromDatabase', 'getTargetAreaStoredInSession');
        $mock = $this->createMock($methods);
        $row = array(
            'pid' => 1
        );
        $relativeRecord = array(
            'tx_flux_column' => 2,
            'tx_flux_parent' => 2,
            'colPos' => ContentService::COLPOS_FLUXCONTENT
        );
        $relativeTo = -1;
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $mock->expects($this->once())->method('loadRecordFromDatabase')->with(1)->will($this->returnValue($relativeRecord));
        #$mock->expects($this->once())->method('updateRecordInDataMap');
        $mock->moveRecord($row, $relativeTo, array(), $tceMain);
        $this->assertEquals($relativeRecord['tx_flux_column'], $row['tx_flux_column']);
        $this->assertEquals($relativeRecord['tx_flux_parent'], $row['tx_flux_parent']);
        $this->assertEquals($relativeRecord['colPos'], $row['colPos']);
        $this->assertEquals(-1, $relativeTo);
    }

    /**
     * @test
     */
    public function loadRecordsFromDatabaseDelegatesToRecordService()
    {
        $mock = new ContentService();
        /** @var WorkspacesAwareRecordService $mockService */
        $mockService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService')->setMethods(array('get'))->getMock();
        $mockService->expects($this->once())->method('get')->with('tt_content', '*', "tx_flux_parent = '123'");
        $mock->injectWorkspacesAwareRecordService($mockService);
        $this->callInaccessibleMethod($mock, 'loadRecordsFromDatabase', 123);
    }

    /**
     * @test
     */
    public function testLoadRecordFromDatabaseWithLanguageUidZero()
    {
        $this->markTestSkippedOnMaster('Skipped on master');
        $mock = new ContentService();
        /** @var WorkspacesAwareRecordService $mockService */
        $mockService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService')->setMethods(array('getSingle'))->getMock();
        $mockService->expects($this->once())->method('getSingle')->with('tt_content', '*');
        $mock->injectWorkspacesAwareRecordService($mockService);
        $this->callInaccessibleMethod($mock, 'loadRecordFromDatabase', 123, 0);
    }

    /**
     * @test
     */
    public function testLoadRecordFromDatabaseWithLanguageUidNotZero()
    {
        $mock = new ContentService();
        /** @var WorkspacesAwareRecordService $mockService */
        $mockService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService')->setMethods(array('getSingle'))->getMock();
        $mockService->expects($this->once())->method('getSingle')->with('tt_content', '*');
        $mock->injectWorkspacesAwareRecordService($mockService);
        $this->callInaccessibleMethod($mock, 'loadRecordFromDatabase', 123, 321);
    }

    /**
     * @test
     */
    public function testupdateRecordInDataMapWithVersionedRecord()
    {
        $row = array(
            'uid' => 123,
            't3ver_oid' => 321,
            'tx_flux_parent' => '3',
            'tx_flux_column' => 'area'
        );
        $mock = $this->objectManager->get($this->createInstanceClassName());
        $this->callInaccessibleMethod($mock, 'updateRecordInDataMap', $row, null, $this->getMockBuilder(DataHandler::class)->getMock());
    }

    /**
     * @test
     * @dataProvider getLanguageInitializationTestValues
     * @param integer $newUid
     * @param integer $oldUid
     * @param integer $newLanguageUid
     * @param integer $fluxParentUid
     * @param boolean $expectsInitialization
     */
    public function testInitializeRecordByNewAndOldAndLanguageUids($newUid, $oldUid, $newLanguageUid, $fluxParentUid, $expectsInitialization)
    {
        if (GeneralUtility::compat_version('8.4.0')) {
            $this->markTestSkipped(
                'Temporarily skipped; see https://review.typo3.org/#/c/50784/ - deleteClause uses DB connection also ' .
                'when no delete field exists which it INTENTIONALLY does not do in our tests for this very reason.'
            );
        }
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('loadRecordFromDatabase', 'updateRecordInDataMap'))->getMock();
        $recordService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\WorkspacesAwareRecordService')->setMethods(array('get'))->getMock();
        $recordService->expects($this->any())->method('get')->willReturn(null);
        $mock->injectWorkspacesAwareRecordService($recordService);
        $dataHandler = $this->getMockBuilder('TYPO3\\CMS\\Core\\DataHandling\\DataHandler')->setMethods(array('resorting'))->getMock();
        $row = array(
          'uid' => 2,
          'pid' => 1,
          'tx_flux_parent' => $fluxParentUid,
          'language' => 1
        );
        $mock->expects($this->once())->method('loadRecordFromDatabase')->will($this->returnValue($row));
        if (true === $expectsInitialization) {
            $mock->expects($this->once())->method('updateRecordInDataMap');
            $dataHandler->expects($this->once())->method('resorting');
        } else {
            $mock->expects($this->never())->method('updateRecordInDataMap');
            $dataHandler->expects($this->never())->method('resorting');
        }
        $this->callInaccessibleMethod(
            $mock,
            'initializeRecordByNewAndOldAndLanguageUids',
            $row,
            $newUid,
            $oldUid,
            $newLanguageUid,
            'language',
            $dataHandler
        );
    }

    /**
     * @return array
     */
    public function getLanguageInitializationTestValues()
    {
        return array(
            array(3, 2, 1, 0, false),
            array(3, 2, 1, 1, false),
            array(3, 2, 2, 0, false),
            array(3, 2, 2, 1, true)
        );
    }

    /**
     * @test
     * @dataProvider getMoveRecordTestValues
     * @param array $parameters
     * @param integer $relativeTo
     */
    public function testMoveRecord($parameters, $relativeTo)
    {
        $row = array(

        );
        $mock = $this->getMockBuilder(
            $this->createInstanceClassName()
        )->setMethods(
            array('loadRecordFromDatabase', 'updateRecordInDataMap', 'updateMovePlaceholder', 'getTargetAreaStoredInSession')
        )->getMock();
        $mock->expects($this->any())->method('loadRecordFromDatabase')->will($this->returnValue($row));
        $mock->expects($this->any())->method('updateRecordInDataMap');
        $mock->expects($this->any())->method('updateMovePlaceholder');
        $dataHandler = $this->getMockBuilder('TYPO3\\CMS\\Core\\DataHandling\\DataHandler')->setMethods(array('resorting'))->getMock();
        $dataHandler->expects($this->any())->method('resorting');
        $result = $mock->moveRecord($row, $relativeTo, $parameters, $dataHandler);
        $this->assertNull($result);
    }

    /**
     * @return array
     */
    public function getMoveRecordTestValues()
    {
        return array(
            array(array('', 'prefix-column-prefix2-unused-unused-top-1-area'), 1),
            array(array('', 'prefix-column-prefix2-unused-unused-top-1-area'), -1),
            array(array('', 'colpos-column-page-unused-unused-top-1-area'), 1),
            array(array('', 'colpos-column-page-unused-unused-top-1-area'), 0 - MiscellaneousUtility::UNIQUE_INTEGER_OVERHEAD - 1),
        );
    }

    /**
     * @param array $functions
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($functions = array())
    {
        $class = substr(get_class($this), 0, -4);
        $class = str_replace('Tests\\Unit\\', '', $class);
        $mock = $this->getMockBuilder($class)->setMethods($functions)->getMock();
        return $mock;
    }

    /**
     * @test
     */
    public function moveRecordWithPositiveRelativeToLoadsRelativeCopiesValuesSetsColumnPositionAndUpdatesRelativeToValue()
    {
        $methods = array('loadRecordFromDatabase', 'getTargetAreaStoredInSession');
        $mock = $this->createMock($methods);
        $row = array(
            'uid' => 31264,
            'tx_flux_column' => 2,
            'tx_flux_parent' => 2,
            'colPos' => ContentService::COLPOS_FLUXCONTENT
        );
        $relativeTo = 2526;
        $parameters = [
            'tt_content',
            '1-paste-2526---0'
        ];
        $tceMain = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        $mock->moveRecord($row, $relativeTo, $parameters, $tceMain);
        $this->assertEquals(null, $row['tx_flux_column']);
        $this->assertEquals(null, $row['tx_flux_parent']);
    }
}
