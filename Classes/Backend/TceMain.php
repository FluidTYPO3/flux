<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * TCEMain
 */
class TceMain
{

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FluxService
     */
    protected $configurationService;

    /**
     * @var RecordService
     */
    protected $recordService;

    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * @var boolean
     */
    static private $cachesCleared = false;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        $this->configurationService = $configurationService;
    }

    /**
     * @param RecordService $recordService
     * @return void
     */
    public function injectRecordService(RecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * @param ContentService $contentService
     * @return void
     */
    public function injectContentService(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->injectObjectManager(GeneralUtility::makeInstance(ObjectManager::class));
        $this->injectConfigurationService($this->objectManager->get(FluxService::class));
        $this->injectRecordService($this->objectManager->get(RecordService::class));
        $this->injectContentService($this->objectManager->get(ContentService::class));
    }

    /**
     * @codingStandardsIgnoreStart
     *
     * The following methods are not covered by coding style checks due to needing
     * non-confirming method names.
     */

    /**
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param string $relativeTo Filled if command is relative to another element
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processCmdmap_preProcess(&$command, $table, $id, &$relativeTo, &$reference)
    {
        $record = (array) $this->recordService->getSingle($table, '*', $id);
        $arguments = ['command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo];
        $this->executeConfigurationProviderMethod(
            'preProcessCommand',
            $table,
            $id,
            $record,
            $arguments,
            $reference
        );
    }

    /**
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param string $relativeTo Filled if command is relative to another element
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference)
    {
        $record = (array) $this->recordService->getSingle($table, '*', $id);
        if ('localize' === $command) {
            $this->contentService->fixPositionInLocalization($id, $relativeTo, $record, $reference);
        }

        if ('move' === $command) {
            // Problem: resolve the potential original record if the passed record is a placeholder.
            // This detection is necessary because 1) the colPos value is an emulated value and exists
            // only in DataHandler::$datamap, but 2) the value in that array is indexed by the original
            // record UID which 3) is not present in neither $row nor $moveRecord.
            // Effect: we have to perform a few extra (tiny) SQL queries here, sadly this cannot be avoided.
            $resolveUid = $this->getOriginalRecordUid($table, $id);

            // Check if pasting is happening via clipboard; if so, extract the parameters for moveRecord.
            // Do so only if we are pasting a content record from clip board.
            $clipboardCommand = (array) $this->getClipboardCommand();
            if (!empty($clipboardCommand['paste']) && strpos($clipboardCommand['paste'], 'tt_content|') === 0) {
                $clipboardCommand = GeneralUtility::trimExplode('|', $clipboardCommand['paste']);
            }

            // Move the immediate record, but only do so if it was a clipboard-initiated command. Other types
            // of move commands are handled by the specific moveRecord hook listeners below.
            if (!empty($clipboardCommand)) {
                $movePlaceholder = BackendUtility::getMovePlaceholder($table, $id);
                if ($GLOBALS['BE_USER']->workspace && $movePlaceholder) {
                    $record = $movePlaceholder;
                    $resolveUid = $movePlaceholder['t3ver_move_id'];
                } else {
                    $record['uid'] = $id;
                }
                $this->contentService->moveRecord($record, $relativeTo, $clipboardCommand, $reference);
                $this->recordService->update($table, $record);
            }

            // Further: if we are moving a placeholder, this implies that a version exists of the original
            // record, and this version will NOT have had the necessary fields updated either.
            // To do this, we resolve the most recent versioned record for our original - and then also
            // update it.
            $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord($table, $resolveUid);
            if ($mostRecentVersionOfRecord) {
                $this->contentService->moveRecord($mostRecentVersionOfRecord, $relativeTo, $clipboardCommand, $reference);
                $this->recordService->update($table, $mostRecentVersionOfRecord);
            }
        }

        $arguments = ['command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo];
        $this->executeConfigurationProviderMethod(
            'postProcessCommand',
            $table,
            $id,
            $record,
            $arguments,
            $reference
        );
    }

    /**
     * @param array $incomingFieldArray The original field names and their values before they are processed
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, &$reference)
    {
        $parameters = GeneralUtility::_GET();
        $this->contentService->affectRecordByRequestParameters($id, $incomingFieldArray, $parameters, $reference);

        $arguments = ['row' => &$incomingFieldArray, 'id' => $id];
        $incomingFieldArray = $this->executeConfigurationProviderMethod(
            'preProcessRecord',
            $table,
            $id,
            $incomingFieldArray,
            $arguments,
            $reference
        );
    }

    /**
     * @param string $status The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param array $fieldArray The field names and their values to be processed
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @return void
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference)
    {
        $arguments = ['status' => $status, 'id' => $id, 'row' => &$fieldArray];
        $fieldArray = $this->executeConfigurationProviderMethod(
            'postProcessRecord',
            $table,
            $id,
            $fieldArray,
            $arguments,
            $reference
        );
    }

    /**
     * @param string $status The command which has been sent to processDatamap
     * @param string $table The table we're dealing with
     * @param mixed $id Either the record UID or a string if a new record has been created
     * @param array $fieldArray The record row how it has been inserted into the database
     * @param DataHandler $reference A reference to the TCEmain instance
     * @return void
     */
    public function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$reference)
    {
        if ('new' === $status && 'tt_content' === $table) {
            $this->contentService->initializeRecord($id, $fieldArray, $reference);
        }
        $arguments = ['status' => $status, 'id' => $id, 'row' => &$fieldArray];
        $fieldArray = $this->executeConfigurationProviderMethod(
            'postProcessDatabaseOperation',
            $table,
            $id,
            $fieldArray,
            $arguments,
            $reference
        );
    }

    /*
     * Methods above are not covered by coding style checks due to needing
     * non-conforming method names.
     *
     * @codingStandardsIgnoreEnd
     */

    /**
     * @param string $table
     * @param integer $uid
     * @param integer $destPid
     * @param array $moveRec
     * @param array $row
     * @param DataHandler $reference
     */
    public function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $row, DataHandler $reference)
    {
        // Problem: resolve the potential original record if the passed record is a placeholder.
        // This detection is necessary because 1) the colPos value is an emulated value and exists
        // only in DataHandler::$datamap, but 2) the value in that array is indexed by the original
        // record UID which 3) is not present in neither $row nor $moveRecord.
        // Effect: we have to perform a few extra (tiny) SQL queries here, sadly this cannot be avoided.
        $resolveUid = $this->getOriginalRecordUid($table, $uid);
        $newColumnNumber = (integer) GeneralUtility::_GET('data')[$table][$resolveUid]['colPos'];

        // Move the immediate record, which may itself be a placeholder or an original record.
        $row['uid'] = $uid;
        $this->contentService->moveRecord($row, $newColumnNumber, [], $reference);
        $this->recordService->update($table, $row);

        // Further: if we are moving a placeholder, this implies that a version exists of the original
        // record, and this version will NOT have had the necessary fields updated either.
        // To do this, we resolve the most recent versioned record for our original - and then also
        // update it.
        $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord($table, $resolveUid);
        if ($mostRecentVersionOfRecord) {
            $this->contentService->moveRecord($mostRecentVersionOfRecord, $newColumnNumber, [], $reference);
            $this->recordService->update($table, $mostRecentVersionOfRecord);
        }
    }


    /**
     * @param string $table
     * @param integer $uid
     * @param integer $destPid
     * @param integer $origDestPid
     * @param array $moveRec
     * @param array $updateFields
     * @param DataHandler $reference
     */
    public function moveRecord_afterAnotherElementPostProcess($table, $uid, $destPid, $origDestPid, $moveRec, &$updateFields, DataHandler $reference)
    {
        // Following block takes care of updating the immediate record, be that a placeholder, an
        // original or a versioned copy.
        $moveData = $this->getMoveData();
        $updateFields['uid'] = $uid;
        $this->contentService->moveRecord($updateFields, $origDestPid, $moveData, $reference);
        $this->recordService->update($table, $updateFields);

        // Further: if we are moving a placeholder, this implies that a version exists of the original
        // record, and this version will NOT have had the necessary fields updated either.
        // To do this, we resolve the most recent versioned record for our original - and then also
        // update it.
        $resolveUid = $this->getOriginalRecordUid($table, $uid);
        $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord($table, $resolveUid);
        if ($mostRecentVersionOfRecord) {
            $this->contentService->moveRecord($mostRecentVersionOfRecord, $origDestPid, [], $reference);
            $this->recordService->update($table, $mostRecentVersionOfRecord);
        }
    }

    /**
     * @param string $table
     * @param integer $uid
     * @return array|bool
     */
    protected function getMostRecentVersionOfRecord($table, $uid)
    {
        if (!$GLOBALS['BE_USER']->workspace) {
            return false;
        }

        return BackendUtility::getWorkspaceVersionOfRecord(
            $GLOBALS['BE_USER']->workspace,
            $table,
            $uid,
            'uid,colPos,tx_flux_parent,tx_flux_column,sorting'
        );
    }

    /**
     * @param string $table
     * @param integer $uid
     * @return integer
     */
    protected function getOriginalRecordUid($table, $uid)
    {
        $placeholder = BackendUtility::getRecord(
            $table,
            $uid,
            't3ver_move_id',
            sprintf(
                ' %s t3ver_state = 3 AND deleted = 0 AND uid = %d',
                version_compare(ExtensionManagementUtility::getExtensionVersion('workspaces'), '8.0.0', '<') ? 'AND' : '',
                $uid
            )
        );

        if ($placeholder) {
            return (integer) $placeholder['t3ver_move_id'];
        }

        return $uid;
    }

    /**
     * Wrapper method to execute a ConfigurationProvider
     *
     * @param string $methodName
     * @param string $table
     * @param mixed $id
     * @param array $record
     * @param array $arguments
     * @param DataHandler $reference
     * @return array
     */
    protected function executeConfigurationProviderMethod(
        $methodName,
        $table,
        $id,
        array $record,
        array $arguments,
        DataHandler $reference
    ) {
        try {
            $id = $this->resolveRecordUid($id, $reference);
            $record = $this->ensureRecordDataIsLoaded($table, $id, $record);
            $arguments['row'] = &$record;
            $arguments[] = &$reference;
            $detectedProviders = $this->configurationService->resolveConfigurationProviders($table, null, $record);
            foreach ($detectedProviders as $provider) {
                call_user_func_array([$provider, $methodName], array_values($arguments));
            }
        } catch (\RuntimeException $error) {
            $this->configurationService->debug($error);
        }
        return $record;
    }

    /**
     * @param string $table
     * @param integer $id
     * @param array $record
     * @return array|NULL
     */
    protected function ensureRecordDataIsLoaded($table, $id, array $record)
    {
        if (true === is_integer($id) && 0 === count($record)) {
            // patch: when a record is completely empty but a UID exists
            $loadedRecord = $this->recordService->getSingle($table, '*', $id);
            $record = true === is_array($loadedRecord) ? $loadedRecord : $record;
        }
        return $record;
    }

    /**
     * @param integer $id
     * @param DataHandler $reference
     * @return integer
     */
    protected function resolveRecordUid($id, DataHandler $reference)
    {
        if (false !== strpos($id, 'NEW')) {
            if (false === empty($reference->substNEWwithIDs[$id])) {
                $id = intval($reference->substNEWwithIDs[$id]);
            }
        } else {
            $id = intval($id);
        }
        return $id;
    }

    /**
     * Perform various cleanup operations upon clearing cache
     *
     * @param string $command
     * @return void
     */
    public function clearCacheCommand($command)
    {
        if (true === self::$cachesCleared) {
            return;
        }
        $tables = array_keys($GLOBALS['TCA']);
        foreach ($tables as $table) {
            $providers = $this->configurationService->resolveConfigurationProviders($table, null);
            foreach ($providers as $provider) {
                /** @var $provider ProviderInterface */
                $provider->clearCacheCommand($command);
            }
        }
        self::$cachesCleared = true;
    }

    /**
     * @return array|NULL
     */
    protected function getMoveData()
    {
        $return = null;
        $rawPostData = $this->getRawPostData();
        if (false === empty($rawPostData)) {
            $request = (array) json_decode($rawPostData, true);
            $hasRequestData = true === isset($request['method']) && true === isset($request['data']);
            $isMoveMethod = 'moveContentElement' === $request['method'];
            $return = (true === $hasRequestData && true === $isMoveMethod) ? $request['data'] : null;
        }
        return $return;
    }

    /**
     * @return array
     */
    protected function getClipboardCommand()
    {
        $command = GeneralUtility::_GET('CB');
        return (array) $command;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    protected function getRawPostData()
    {
        return file_get_contents('php://input');
    }
}
