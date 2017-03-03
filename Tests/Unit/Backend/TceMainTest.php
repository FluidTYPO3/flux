<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\TceMain;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * TceMainTest
 */
class TceMainTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function setUp()
    {
        $configurationManager = $this->getMockBuilder('FluidTYPO3\Flux\Configuration\ConfigurationManager')->getMock();
        $fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
        $fluxService->injectConfigurationManager($configurationManager);
        $GLOBALS['TYPO3_DB'] = $this->getMockBuilder(
            'TYPO3\\CMS\\Core\\Database\\DatabaseConnection'
        )->setMethods(
            array('exec_SELECTgetSingleRow', 'exec_SELECTgetRows')
        )->disableOriginalConstructor()->getMock();
        $GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(false);
        $GLOBALS['TCA'] = array(
            'tt_content' => array(
                'columns' => array(
                    'pi_flexform' => array()
                )
            )
        );
    }

    /**
     * @test
     */
    public function canExecuteClearAllCacheCommandAndPassToProvidersForEveryTcaTable()
    {
        $instance = $this->getInstance();
        $mockedFluxService = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('resolveConfigurationProviders'))->getMock();
        $mockedProvider = $this->getMockBuilder('FluidTYPO3\Flux\Provider\Provider')->setMethods(array('clearCacheCommand'))->getMock();
        $expectedExecutions = count($GLOBALS['TCA']);
        $mockedProvider->expects($this->exactly($expectedExecutions))->method('clearCacheCommand')->with('all');
        $mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue(array($mockedProvider)));
        ObjectAccess::setProperty($instance, 'configurationService', $mockedFluxService, true);
        $instance->clearCacheCommand('all');
    }

    /**
     * @test
     */
    public function canExecuteClearAllCacheCommandTwiceWithoutDoubleCalling()
    {
        $instance = $this->getInstance();
        $mockedFluxService = $this->getMockBuilder('FluidTYPO3\Flux\Service\FluxService')->setMethods(array('resolveConfigurationProviders'))->getMock();
        $mockedFluxService->expects($this->atLeastOnce())->method('resolveConfigurationProviders')->will($this->returnValue(array()));
        ObjectAccess::setProperty($instance, 'configurationService', $mockedFluxService, true);
        $instance->clearCacheCommand('all');
        $instance->clearCacheCommand('all');
    }

    /**
     * @test
     */
    public function canExecuteDataPreProcessHook()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $result = $instance->processDatamap_preProcessFieldArray($record, 'tt_content', $record['uid'], $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteDataPreProcessHookWithoutRecord()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = array();
        $result = $instance->processDatamap_preProcessFieldArray($record, 'tt_content', null, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteDataPostProcessHook()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $result = $instance->processDatamap_postProcessFieldArray('update', 'tt_content', $record['uid'], $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteDataPostProcessHookWithoutRecord()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = array();
        $result = $instance->processDatamap_postProcessFieldArray('update', 'tt_content', null, $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteAfterDatabaseOperationHook()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', $record['uid'], $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteAfterDatabaseOperationHookWithoutRecord()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = array();
        $result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', null, $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteAfterDatabaseOperationHookWithNewRecord()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = array(
            'hidden' => 0
        );
        $result = $instance->processDatamap_afterDatabaseOperations('update', 'tt_content', 'NEW4cds44', $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteCommandPreProcessHook()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $command = 'update';
        $result = $instance->processCmdmap_preProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteCommandPreProcessHookWithNullRecord()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = null;
        $command = 'update';
        $result = $instance->processCmdmap_preProcess($command, 'tt_content', 'NEW532cf4', $record, $tceMainParent);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function canExecuteCommandPostProcessHook()
    {
        $instance = $this->getInstance();
        $tceMainParent = $this->getCallerInstance();
        $record = Records::$contentRecordWithoutParentAndWithoutChildren;
        $command = 'update';
        $pasteUpdate = false;
        $pasteMap = [];
        $result = $instance->processCmdmap_postProcess($command, 'tt_content', $record['uid'], $record, $tceMainParent, $pasteUpdate, $pasteMap);
        $this->assertNull($result);
    }

    /**
     * @return DataHandler
     */
    protected function getCallerInstance()
    {
        /** @var DataHandler $tceMainParent */
        $tceMainParent = GeneralUtility::makeInstance('TYPO3\CMS\Core\DataHandling\DataHandler');
        return $tceMainParent;
    }

    /**
     * @return TceMain
     */
    protected function getInstance()
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        $tceMainInstance = new TceMain();
        ObjectAccess::setProperty($tceMainInstance, 'cachesCleared', false, true);
        return $tceMainInstance;
    }

    /**
     * @test
     */
    public function executeConfigurationProviderMethodDebugsOnException()
    {
        $exception = new \RuntimeException();
        $mock = new TceMain();
        $configurationService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('debug', 'resolveConfigurationProviders'))->getMock();
        $configurationService->expects($this->once())->method('debug')->with($exception);
        $configurationService->expects($this->once())->method('resolveConfigurationProviders')->will($this->throwException($exception));
        $handler = new DataHandler();
        $record = array();
        $parameters = array();
        $handler->substNEWwithIDs['NEW123'] = 123;
        $mock->injectConfigurationService($configurationService);
        $result = $this->callInaccessibleMethod(
            $mock,
            'executeConfigurationProviderMethod',
            'method',
            'tt_content',
            'NEW123',
            $record,
            $parameters,
            $handler
        );
        $this->assertEmpty($result);
    }

    /**
     * @test
     */
    public function executeConfigurationProviderMethodCallsMethodOnProviders()
    {
        $command = 'postProcessDatabaseOperation';
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('resolveRecordUid', 'ensureRecordDataIsLoaded'))->getMock();
        $mock->expects($this->once())->method('resolveRecordUid')->willReturn(1);
        $mock->expects($this->once())->method('ensureRecordDataISLoaded')->willReturnArgument(2);
        $caller = $this->getCallerInstance();
        $row = array('uid' => 1);
        $arguments = array('status' => $command, 'id' => 1, 'row' => $row);
        $provider = $this->getMockBuilder('FluidTYPO3\\Flux\\Provider\\Provider')->setMethods(array($command))->getMock();
        $provider->expects($this->exactly(2))->method($command);
        $providers = array($provider, $provider);
        $configurationService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('resolveConfigurationProviders'))->getMock();
        $configurationService->expects($this->once())->method('resolveConfigurationProviders')->willReturn($providers);
        $mock->injectConfigurationService($configurationService);
        $result = $this->callInaccessibleMethod($mock, 'executeConfigurationProviderMethod', $command, 'void', 1, $row, $arguments, $caller);
        $this->assertEquals($row, $result);
    }

    /**
     * @test
     * @dataProvider getResolveRecordUidTestValues
     * @param mixed $input
     * @param mixed $handlerInput
     * @param integer $expectedOutput
     */
    public function testResolveRecordUid($input, $handlerInput, $expectedOutput)
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('dummy'))->disableOriginalConstructor()->getMock();
        $dataHandler = new DataHandler();
        if (null !== $handlerInput) {
            $dataHandler->substNEWwithIDs[$input] = $handlerInput;
        }
        $result = $this->callInaccessibleMethod($instance, 'resolveRecordUid', $input, $dataHandler);
        $this->assertTrue($expectedOutput === $result, 'Resolved record UID was not expected value');
    }

    /**
     * @return array
     */
    public function getResolveRecordUidTestValues()
    {
        return array(
            array('123', null, 123),
            array('NEW123', '123', 123),
            array('', null, 0)
        );
    }

    /**
     * @test
     */
    public function postProcessDatabaseOperationWithNewStatusAndContentTableCallsInitializeRecord()
    {
        $contentService = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\ContentService')->setMethods(array('initializeRecord'))->getMock();
        $contentService->expects($this->once())->method('initializeRecord');
        /** @var DataHandler $tceMain */
        $tceMain = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('executeConfigurationProviderMethod'))->disableOriginalConstructor()->getMock();
        $instance->injectContentService($contentService);
        $row = array();
        $instance->processDatamap_afterDatabaseOperations('new', 'tt_content', 1, $row, $tceMain);
    }

    /**
     * @test
     * @dataProvider getMoveDataTestvalues
     * @param mixed $postData
     * @param string|NULL $expected
     */
    public function getMoveDataReturnsExpectedValues($postData, $expected)
    {
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getRawPostData'))->getMock();
        $instance->expects($this->once())->method('getRawPostData')->willReturn($postData);
        $result = $this->callInaccessibleMethod($instance, 'getMoveData');
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getMoveDataTestvalues()
    {
        return array(
            array(null, null),
            array('{}', null),
            array('{"method": "test"}', null),
            array('{"method": "test", "data": []}', null),
            array('{"method": "moveContentElement", "data": "test"}', 'test'),
        );
    }
}
