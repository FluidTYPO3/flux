<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Interfaces\CommandProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\ContentService;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
     * Method to initialize the command processing map with a single purpose:
     * to re-sort any "swap" operations to put the operation happening to the
     * parent record, after all operations happening to child records, and
     * do so only for the tt_content table.
     *
     * @param DataHandler $reference
     * @return void
     */
    public function processCmdmap_beforeStart(&$reference)
    {
        if (empty($reference->cmdmap['tt_content'])) {
            return;
        }
        $parents = [];
        $children = [];
        $others = [];
        $remap = false;
        foreach ($reference->cmdmap['tt_content'] as $uid => $command) {
            if (empty($command['version'])) {
                if (isset($command['copy']['update']['colPos']) && $command['copy']['update']['colPos'] > ContentService::COLPOS_FLUXCONTENT) {
                    // copy during drag and drop in flux grid columns
                    list($command['copy']['update']['tx_flux_parent'], $command['copy']['update']['tx_flux_column']) =
                        $this->contentService->getTargetAreaStoredInSession($command['copy']['update']['colPos']);
                    $command['copy']['update']['colPos'] = ContentService::COLPOS_FLUXCONTENT;
                    $remap = true;
                }
                $others[$uid] = $command;
            } elseif ($command['version']['action'] === 'swap') {
                $remap = true;
                if (count($this->recordService->get('tt_content', 'uid', sprintf('tx_flux_parent = %d', $uid)))) {
                    $parents[$uid] = $command;
                } else {
                    $children[$uid] = $command;
                }
            }
        }
        if ($remap) {
            $reference->cmdmap['tt_content'] = $children + $parents + $others;
        }
    }

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
        $record = $this->resolveRecordForOperation($table, $id);

        if ($table === 'tt_content') {
            $properties = [];
            $clipboardCommand = (array) $this->getClipboardCommand();
            if (!empty($clipboardCommand['paste']) && strpos($clipboardCommand['paste'], 'tt_content|') === 0) {
                $properties = (array) $clipboardCommand['update'];
                $clipboardCommand = GeneralUtility::trimExplode('|', $clipboardCommand['paste']);
            }

            // We only want to process clipboard commands, since these do not trigger the moveRecord hooks below
            // and no other hooks catch copy operations.
            if (!empty($clipboardCommand)) {
                if ($command === 'copy' || $command === 'move') {

                    if ($command === 'copy') {
                        // When "copy" is received as command, this method unfortunately receives the original
                        // record and we now must attempt to find the newly created copy (or placeholder thereof) instead.
                        $record = $this->resolveRecordForOperation($table, $reference->copyMappingArray[$table][$id]);
                    }

                    foreach ($properties as $propertyName => $propertyValue) {
                        $record[$propertyName] = $propertyValue;
                    }

                    // Guard: do not allow records to become children of themselves at any recursion level.
                    // Only perform this check if the "relativeTo" target is a negative integer meaning
                    // "insert after the record with uid=abs($relativeTo)". When moving to a page column
                    // the $relativeTo value is a positive integer and we will skip it.
                    if ($command === 'move' && $relativeTo <= 0) {
                        // Perform an unpersisted record moving to perform assertions on the result.
                        $temporaryRecord = $record;
                        $this->contentService->moveRecord($temporaryRecord, $relativeTo, $clipboardCommand, $reference);

                        $relativeRecordUid = abs($reference->cmdmap[$table][$id]['move']);
                        $relativeRecord = $this->getRecordWithoutRestrictions($table, $relativeRecordUid, 'uid,pid,tx_flux_parent');
                        BackendUtility::workspaceOL($table, $relativeRecord);

                        if ($this->isRecordChildOfItself($table, $temporaryRecord)) {
                            $message = new FlashMessage(
                                sprintf(
                                    'Attempt to move record %s:%d into a column of a child of itself. Move aborted.',
                                    $table,
                                    $id
                                ),
                                'Error during ' . $command,
                                FlashMessage::ERROR,
                                true
                            );

                            /** @var FlashMessageService $flashMessageService */
                            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
                            $flashMessageService->getMessageQueueByIdentifier()->enqueue($message);

                            // Remove the mapped command in order to avoid DataHandler calling "processcmdmap" hooks which may
                            // attempt to perform the command.
                            unset($reference->cmdmap[$table][$id]);

                            // Nullify the command so DataHandler will not process the command either.
                            $command = null;

                            // Return from this hook to avoid calling Flux Providers with an invalid command setup.
                            return;
                        }
                    }
                }
            }
        }

        $arguments = ['command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo];
        $this->executeConfigurationProviderMethod(
            'preProcessCommand',
            $table,
            $id,
            $record,
            $arguments,
            CommandProviderInterface::class,
            $reference
        );
    }

    /**
     * Command post processing method
     *
     * Like other pre/post methods this method calls the corresponding
     * method on Providers which match the table/id(record) being passed.
     *
     * In addition, this method also listens for paste commands executed
     * via the TYPO3 clipboard, since such methods do not necessarily
     * trigger the "normal" record move hooks (which we also subscribe
     * to and react to in moveRecord_* methods).
     *
     * @param string $command The TCEmain operation status, fx. 'update'
     * @param string $table The table TCEmain is currently processing
     * @param string $id The records id (if any)
     * @param string $relativeTo Filled if command is relative to another element
     * @param DataHandler $reference Reference to the parent object (TCEmain)
     * @param array $pasteUpdate
     * @param array $pasteDataMap
     * @return void
     */
    public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference, &$pasteUpdate, &$pasteDataMap)
    {
        $record = $this->resolveRecordForOperation($table, $id);

        if ($table === 'tt_content') {
            if ('localize' === $command) {
                $this->contentService->fixPositionInLocalization($id, $relativeTo, $record, $reference);
            }

            $properties = [];
            $clipboardCommand = (array) $this->getClipboardCommand();
            if (!empty($clipboardCommand['paste']) && strpos($clipboardCommand['paste'], 'tt_content|') === 0) {
                $properties = (array) $clipboardCommand['update'];
                $clipboardCommand = GeneralUtility::trimExplode('|', $clipboardCommand['paste']);

                // Special case: 8.6 sends clipboard pasting commands in a way that matches this case.
                // When we encounter a Flux-handled colPos value we perform a move on the input maps
                // and reset the clipboard command and relativeTo so ContentService resolves from colPos.
                if ($properties['colPos'] > ContentService::COLPOS_FLUXCONTENT) {
                    $relativeTo = $properties['colPos'];
                    $clipboardCommand = [];

                    $this->contentService->moveRecord($pasteDataMap[$table][$id], $relativeTo, $clipboardCommand, $reference);
                    $this->contentService->moveRecord($properties, $relativeTo, $clipboardCommand, $reference);

                }
            }

            // We only want to process clipboard commands, since these do not trigger the moveRecord hooks below
            // and no other hooks catch copy operations.
            if (!empty($clipboardCommand)) {
                if ($command === 'copy' || $command === 'move') {

                    if ($command === 'copy') {
                        // When "copy" is received as command, this method unfortunately receives the original
                        // record and we now must attempt to find the newly created copy (or placeholder thereof) instead.
                        $record = $this->resolveRecordForOperation($table, $reference->copyMappingArray[$table][$id]);
                    }

                    foreach ($properties as $propertyName => $propertyValue) {
                        $record[$propertyName] = $propertyValue;
                    }

                    $this->contentService->moveRecord($record, $relativeTo, $clipboardCommand, $reference);
                    $this->recordService->update($table, $record);
                    $this->moveChildPlaceholdersToPageUid([$id], $record['pid']);

                    $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord(
                        $table,
                        $record['t3ver_move_id'] > 0 ? $record['t3ver_move_id'] : $record['uid']
                    );
                    if ($mostRecentVersionOfRecord) {
                        $this->contentService->moveRecord($mostRecentVersionOfRecord, $relativeTo, $clipboardCommand, $reference);
                        $this->recordService->update($table, $mostRecentVersionOfRecord);
                    }
                }
            }

            if ($command === 'copy') {
                // We now check if we're doing a copy command once again. Due to internals of TYPO3, child
                // records of the parent being moved (if child records exist) have now gotten their sorting
                // values reset. The only way currently to patch this is a semi-expensive recursive operation
                // to copy the sorting value from the original record to the copy (and to the placeholder and
                // versioned records if those exist). This is less than ideal but the alternative is
                // consistently wrong (read: sql insertion order based, to end user same as random) sorting of
                // *all* child records after a copy operation - so we compromise.
                $this->copySortingValueOfChildrenFromOriginalsToCopies(
                    $this->resolveRecordForOperation($table, $reference->copyMappingArray[$table][$id])
                );
            } elseif ($command === 'move') {
                $this->updateSortingValue(
                    sprintf(
                        't.sorting != s.sorting AND t.t3ver_move_id = s.t3ver_oid AND s.t3ver_state = 4 ' .
                        'AND s.tx_flux_parent = %d AND s.t3ver_wsid = %d',
                        $id,
                        $GLOBALS['BE_USER']->workspace
                    )
                );
            }
        }

        $arguments = ['command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo];
        $this->executeConfigurationProviderMethod(
            'postProcessCommand',
            $table,
            $id,
            $record,
            $arguments,
            CommandProviderInterface::class,
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
            RecordProviderInterface::class,
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
            RecordProviderInterface::class,
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
        if ($status === 'update' && $table === 'tt_content' && $GLOBALS['BE_USER']->workspace) {

            // We fix a side effect caused by the IRRE relation between parent and child. When a parent is
            // moved and we are inside a workspace, the parent UID remains the same which causes TYPO3 to
            // update the live records with an incorrect (sequential) sorting number.

            // Unfortunately we cannot prevent the live child records from receiving a new sorting value,
            // but at least the new values are in the correct order. The best we can do is to update the
            // workspace move placeholder with sorting values from the versioned record - this causes the
            // workspace preview to be in the correct sorting order, and restores the proper sorting value
            // to child records when the workspace is published.
            $originalUid = BackendUtility::getLiveVersionIdOfRecord($table, $id);
            GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content')->query(
                sprintf(
                    'UPDATE tt_content t, tt_content s SET t.sorting = s.sorting WHERE t.sorting != s.sorting ' .
                    'AND t.t3ver_move_id = s.t3ver_oid AND s.t3ver_state = 4 AND s.tx_flux_parent = %d AND s.t3ver_wsid = %d',
                    $originalUid,
                    $GLOBALS['BE_USER']->workspace
                )
            )->execute();
        }
        $arguments = ['status' => $status, 'id' => $id, 'row' => &$fieldArray];
        $fieldArray = $this->executeConfigurationProviderMethod(
            'postProcessDatabaseOperation',
            $table,
            $id,
            $fieldArray,
            $arguments,
            CommandProviderInterface::class,
            $reference
        );
    }

    /**
     * Hook method which listens only to operations which move the record
     * to the first position in a (page-level, not Flux nested) column.
     * The method will only process the "root" record, e.g. the method
     * gets called for every child record but only performs any operations
     * on the parent record.
     *
     * Must use a slightly esoteric method of resolving the input argument
     * value for "new column number" which gets passed only in GET.
     *
     * @param string $table
     * @param integer $uid
     * @param integer $destPid
     * @param array $moveRec
     * @param array $row
     * @param DataHandler $reference
     */
    public function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $row, DataHandler $reference)
    {
        if ($table !== 'tt_content') {
            return;
        }

        // Problem: resolve the potential original record if the passed record is a placeholder.
        // This detection is necessary because 1) the colPos value is an emulated value and exists
        // only in DataHandler::$datamap, but 2) the value in that array is indexed by the original
        // record UID which 3) is not present in neither $row nor $moveRec.
        // Effect: we have to perform a few extra (tiny) SQL queries here, sadly this cannot be avoided.
        $resolveUid = $this->getOriginalRecordUid($table, $uid);
        $newColumnNumber = GeneralUtility::_GET('data')[$table][$resolveUid]['colPos'];
        if ($newColumnNumber === null && is_array($reference->cmdmap[$table][$resolveUid]['move']['update'] ?? null)) {
            $newColumnNumber = $reference->cmdmap[$table][$resolveUid]['move']['update']['colPos'];
        }

        // The following code must NOT execute if the new column number was not provided in the exact
        // required place specified above. For all other cases we do NOT want to react to this hook,
        // such other cases include cascaded operations on all child content. To put it shortly:
        // this prevents performing moves on ANY other record than the exact input record itself.
        if ($newColumnNumber === null) {
            return;
        }

        // Move the immediate record, which may itself be a placeholder or an original record.
        $row['uid'] = $uid;
        $target = [$table, $destPid];
        $this->contentService->moveRecord($row, $destPid, $target, $reference);
        $this->recordService->update($table, $row);
        $reference->updateRefIndex($table, $uid);

        $this->moveChildPlaceholdersToPageUid([$resolveUid], $destPid);

        // Further: if we are moving a placeholder, this implies that a version exists of the original
        // record, and this version will NOT have had the necessary fields updated either.
        // To do this, we resolve the most recent versioned record for our original - and then also
        // update it.
        $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord(
            $table,
            $row['t3ver_move_id'] > 0 ? $row['t3ver_move_id'] : $resolveUid
        );
        if ($mostRecentVersionOfRecord) {
            $this->contentService->moveRecord($mostRecentVersionOfRecord, $destPid, $target, $reference);
            $this->recordService->update($table, $mostRecentVersionOfRecord);
            $reference->updateRefIndex($table, $mostRecentVersionOfRecord['uid']);
        }
    }

    /**
     * Hook method listening specifically for operations which move a
     * record relative to (after) another element.
     *
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
        if ($table !== 'tt_content') {
            return;
        }

        // Following block takes care of updating the immediate record, be that a placeholder, an
        // original or a versioned copy.
        $moveData = $this->getMoveData();
        if (!$moveData) {
            return;
        }
        $updateFields['uid'] = $uid;

        $this->contentService->moveRecord($updateFields, $origDestPid, $moveData, $reference);

        // Guard: do not allow records to become children of themselves at any recursion level.
        // Must be performed after the "moveRecord" above, which does *not* update the DB. We
        // validate on the result of the (unpersisted) move operation.
        if ($this->isRecordChildOfItself($table, $updateFields)) {
            return;
        }

        $this->recordService->update($table, $updateFields);
        $reference->updateRefIndex($table, $uid);

        // Further: if we are moving a placeholder, this implies that a version exists of the original
        // record, and this version will NOT have had the necessary fields updated either.
        // To do this, we resolve the most recent versioned record for our original - and then also
        // update it.
        $resolveUid = $this->getOriginalRecordUid($table, $uid);
        $this->moveChildPlaceholdersToPageUid([$resolveUid], $destPid);
        $mostRecentVersionOfRecord = $this->getMostRecentVersionOfRecord($table, $resolveUid);
        if ($mostRecentVersionOfRecord) {
            $this->contentService->moveRecord($mostRecentVersionOfRecord, $origDestPid, $moveData, $reference);
            $this->recordService->update($table, $mostRecentVersionOfRecord);
            $reference->updateRefIndex($table, $mostRecentVersionOfRecord['uid']);
        }
    }

    /*
     * Methods above are not covered by coding style checks due to needing
     * non-conforming method names.
     *
     * @codingStandardsIgnoreEnd
     */

    /**
     * Moves all placeholder children of $parentUid to $pageUid. This method
     * solves a TYPO3 core bug in which child records' PID value is not updated
     * when the parent record is moved.
     *
     * Only targets move placeholders since only move placeholders are affected.
     *
     * @param array $parentUids
     * @param integer $pageUid
     * @return void
     */
    protected function moveChildPlaceholdersToPageUid(array $parentUids, $pageUid)
    {
        if (!$GLOBALS['BE_USER']->workspace) {
            return;
        }
        $childMovePlaceholders = $this->recordService->get(
            'tt_content',
            'uid',
            sprintf(
                'tx_flux_parent IN (%s) AND t3ver_state = 3 AND t3ver_wsid > 0',
                implode(', ', array_map('intval', $parentUids))
            )
        );
        if (empty($childMovePlaceholders)) {
            return;
        }
        $childMovePlaceholderUids = array_column($childMovePlaceholders, 'uid');
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->update('tt_content')
            ->set('pid', $pageUid, true)
            ->where($queryBuilder->expr()
            ->in('uid', implode(', ', array_map('intval', $childMovePlaceholderUids))));
        $childMovePlaceholderUids = HookHandler::trigger(
            HookHandler::RECORD_CHILD_PLACEHOLDERS_MOVED,
            [
                'parentUids' => $parentUids,
                'pageUid' => $pageUid,
                'childPlaceholderUids' => $childMovePlaceholders
            ]
        )['childPlaceholderUids'];
        $this->moveChildPlaceholdersToPageUid($childMovePlaceholderUids, $pageUid);
    }

    /**
     * Fixes an issue with records after they have been copied. Sorting numbers
     * of all child records (to infinite recursion depth) have been completely
     * mangled by TYPO3 and are now a series of sequential numbers rather than
     * the generously spaced sorting values the `tt_content` table needs.
     *
     * The function iterates recursively to perform SQL queries which override
     * the new sorting values, copying the ones from the original record.
     *
     * @param array $parentRecord
     * @return void
     */
    protected function copySortingValueOfChildrenFromOriginalsToCopies(array $parentRecord)
    {
        if (!$parentRecord['uid']) {
            // make sure value is set (#1407)
            return;
        }

        $children = $this->recordService->get(
            'tt_content',
            'uid',
            sprintf('tx_flux_parent = %d', $parentRecord['uid'])
        );

        if (!count($children)) {
            return;
        }

        foreach ($children as $child) {

            // Perform an SQL query which directly copies the original record's sorting number to the copy.
            // When not in a workspace: copy the sorting field value from original to copy.
            // When in a workspace: copy the sorting field value from original to versioned record and move placeholder.
            if ($GLOBALS['BE_USER']->workspace) {

                // Update versioned record (which is what $parentRecord is when copy happens in workspace mode)
                $this->updateSortingValue(
                    sprintf(
                        't.uid = %d AND s.uid = t.t3_origuid',
                        $child['uid']
                    )
                );

                // Update the move placeholder that was automatically created for the versioned record we updated above.
                $this->updateSortingValue(
                    sprintf(
                        't.t3ver_oid = %d AND s.uid = t.t3ver_oid AND t.t3ver_state = -1',
                        $child['uid']
                    )
                );

            } else {
                $this->updateSortingValue(sprintf('t.uid = %d AND s.uid = t.t3_origuid', $child['uid']));
            }
            $this->copySortingValueOfChildrenFromOriginalsToCopies($child);
        }
    }

    /**
     * Updates sorting values of tt_content aliased as "t" by
     * copying sorting value from tt_content aliased as "s"
     * with the destination and source records identified by
     * the condition (for example, t.uid = 123 AND s.uid = 321
     * copies sorting from record with uid 321 to uid 123)
     *
     * @param string $condition
     */
    protected function updateSortingValue($condition)
    {
        $query = sprintf(
            'UPDATE tt_content t, tt_content s SET t.sorting = s.sorting WHERE %s',
            $condition
        );
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');
        $connection->query($query)->execute();
        HookHandler::trigger(
            HookHandler::RECORD_CONTENT_SORTED,
            [
                'condition' => $condition
            ]
        );
    }

    /**
     * Validates that $record was not requested to be moved (only move,
     * not copy) into a child column of itself, or a child column of a
     * child column, etc.
     *
     * Returns true if record would become a child of itself.
     *
     * @param string $table
     * @param array $record
     * @return boolean
     */
    protected function isRecordChildOfItself($table, array $record)
    {
        $childUid = $record['uid'];
        do {
            $movePlaceholder = BackendUtility::getMovePlaceholder($table, $record['uid']);
            if ($movePlaceholder) {
                $record = $movePlaceholder;
            }
            // Loop through records starting with the input record, verifying that none
            // of the records' parents are the same as the input record.
            if ((integer) $childUid === (integer) $record['tx_flux_parent']) {
                return true;
            }
        } while (
            $record['tx_flux_parent'] > 0
            && ($record = $this->getRecordWithoutRestrictions($table, $record['tx_flux_parent'], 'uid,tx_flux_parent')
            )
        );

        return false;
    }

    /**
     * If we are in a workspace, this method returns the most recent version
     * of the original record - if we are not in a workspace or the record
     * has not yet been versioned, `false` is returned.
     *
     * @param string $table
     * @param integer $uid
     * @return array|boolean
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
            'uid,colPos,tx_flux_parent,tx_flux_column,sorting,t3ver_move_id'
        );
    }

    /**
     * Returns either the record itself (if we are not in a workspace)
     * or the move placeholder for the record if we are.
     *
     * Therefore, this method is only appropriate for "copy" and "move"
     * operations which by nature must be done on placeholders in workspace.
     *
     * @param string $table
     * @param integer $id
     * @return array|boolean
     */
    protected function resolveRecordForOperation($table, $id)
    {
        $record = (array) $this->recordService->getSingle($table, '*', $id);

        if ($GLOBALS['BE_USER']->workspace) {

            $movePlaceholder = BackendUtility::getMovePlaceholder($table, $id);
            if ($movePlaceholder) {
                $record = $movePlaceholder;
            } else {
                $record['uid'] = $id;
            }

        }

        return HookHandler::trigger(
            HookHandler::RECORD_RESOLVED,
            [
                'record' => $record,
                'table' => $table,
                'id' => $id
            ]
        )['record'];
    }

    /**
     * @param string $table
     * @param integer $uid
     * @return integer
     */
    protected function getOriginalRecordUid($table, $uid)
    {
        $placeholder = $this->recordService->get(
            $table,
            't3ver_move_id',
            sprintf(
                't3ver_state = 3 AND deleted = 0 AND uid = %d',
                $uid
            )
        );

        if ($placeholder[0] ?? false) {
            return (integer) $placeholder[0]['t3ver_move_id'];
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
     * @param string|array $interfaces
     * @param DataHandler $reference
     * @return array
     */
    protected function executeConfigurationProviderMethod(
        $methodName,
        $table,
        $id,
        array $record,
        array $arguments,
        $interfaces = ProviderInterface::class,
        DataHandler $reference
    ) {
        $id = $this->resolveRecordUid($id, $reference);
        $record = $this->ensureRecordDataIsLoaded($table, $id, $record);
        $arguments['row'] = &$record;
        $arguments[] = &$reference;
        $detectedProviders = $this->configurationService->resolveConfigurationProviders(
            $table,
            null,
            $record,
            null,
            $interfaces
        );
        foreach ($detectedProviders as $provider) {
            call_user_func_array([$provider, $methodName], array_values($arguments));
        }
        return HookHandler::trigger(
            HookHandler::PROVIDER_COMMAND_EXECUTED,
            [
                'record' => $record,
                'providers' => $detectedProviders,
                'command' => $methodName,
                'table' => $table,
                'id' => $id,
                'interfaces' => $interfaces,
                'dataHandler' => $reference
            ]
        )['record'];
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
        if (true === static::$cachesCleared) {
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
        static::$cachesCleared = true;
        HookHandler::trigger(
            HookHandler::CACHES_CLEARED,
            [
                'command' => $command
            ]
        );
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

    /**
     * @param string $table
     * @param integer $uid
     * @param string $fields
     * @return array|null
     */
    protected function getRecordWithoutRestrictions($table, $uid, $fields)
    {
        $builder = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table)->createQueryBuilder();
        $builder->getRestrictions()->removeAll();
        $builder->select(...explode(',', $fields))->from($table)->where(sprintf('uid = %d', $uid));
        return $builder->execute()->fetch();
    }
}
