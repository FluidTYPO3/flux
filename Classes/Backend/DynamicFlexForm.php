<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Dynamic FlexForm insertion hook class
 */
class DynamicFlexForm extends FlexFormTools
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
     * @var WorkspacesAwareRecordService
     */
    protected $recordService;

    /**
     * @var boolean
     */
    protected static $recursed = false;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param FluxService $service
     * @return void
     */
    public function injectConfigurationService(FluxService $service)
    {
        $this->configurationService = $service;
    }

    /**
     * @param WorkspacesAwareRecordService $recordService
     * @return void
     */
    public function injectRecordService(WorkspacesAwareRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * DynamicFlexForm constructor.
     */
    public function __construct()
    {
        $this->injectObjectManager(GeneralUtility::makeInstance(ObjectManager::class));
        $this->injectConfigurationService($this->objectManager->get(FluxService::class));
        $this->injectRecordService($this->objectManager->get(WorkspacesAwareRecordService::class));
    }

    /**
     * Method to generate a custom identifier for a Flux-based DS.
     * The custom identifier must include a record ID, which we
     * can then use to restore the record.
     *
     * @param array $tca
     * @param $tableName
     * @param $fieldName
     * @param array $record
     * @return array
     */
    public function getDataStructureIdentifierPreProcess(array $tca, $tableName, $fieldName, array $record)
    {
        if (static::$recursed) {
            return [];
        }

        // Select a limited set of the $record being passed. When the $record is a new record, it will have
        // no UID but will contain a list of default values, in which case we extract a smaller list of
        // values based on the "useColumnsForDefaultValues" TCA control (we mimic the amount of data that
        // would be available via the new content wizard). If the record has a UID we record only the UID.
        // In the latter case we sacrifice some performance (having to reload the record by UID) in order
        // to pass an identifier small enough to be part of GET parameters. This class will then "thaw" the
        // record identified by UID to ensure that for all existing records, Providers receive the FULL data.
        if ((integer) $record['uid']) {
            $limitedRecordData = ['uid' => $record['uid']];
        } else {
            $fields = GeneralUtility::trimExplode(',', $GLOBALS['TCA'][$tableName]['ctrl']['useColumnsForDefaultValues']);
            if ($GLOBALS['TCA'][$tableName]['ctrl']['type'] ?? false) {
                $fields[] = $GLOBALS['TCA'][$tableName]['ctrl']['type'];
                if ($GLOBALS['TCA'][$tableName]['ctrl'][$GLOBALS['TCA'][$tableName]['ctrl']['type']]['subtype_value_field'] ?? false) {
                    $fields[] = $GLOBALS['TCA'][$tableName]['ctrl'][$GLOBALS['TCA'][$tableName]['ctrl']['type']]['subtype_value_field'];
                }
            }
            $fields = array_combine($fields, $fields);
            $limitedRecordData = array_intersect_key($record, $fields);
            $limitedRecordData[$fieldName] = $record[$fieldName];
        }
        $providers = $this->configurationService->resolveConfigurationProviders($tableName, $fieldName, $record);
        if (count($providers) === 0) {
            return [];
        }
        static::$recursed = true;
        $identifier = [
            'type' => 'flux',
            'tableName' => $tableName,
            'fieldName' => $fieldName,
            'record' => $limitedRecordData,
            'originalIdentifier' => $this->getDataStructureIdentifier(
                [ 'config' => $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config']],
                $tableName,
                $fieldName,
                $record
            )
        ];
        static::$recursed = false;
        return $identifier;
    }

    /**
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPreProcess(array $identifier)
    {
        if ($identifier['type'] !== 'flux') {
            return [];
        }
        $record = $identifier['record'];
        if (!$record) {
            return [];
        }

        $fromCache = $this->configurationService->getFromCaches($identifier);
        if ($fromCache) {
            return $fromCache;
        }
        if (count($record) === 1 && isset($record['uid'])) {
            $record = BackendUtility::getRecord($identifier['tableName'], $record['uid'], '*', '', false);
        }
        $fieldName = $identifier['fieldName'];
        $dataStructArray = $dataStructureArray = $this->parseDataStructureByIdentifier($identifier['originalIdentifier']);;
        $providers = $this->configurationService->resolveConfigurationProviders($identifier['tableName'], $fieldName, $record);
        if (count($providers) === 0) {
            // No Providers detected - we will cache this response
            $this->configurationService->setInCaches([], true, $identifier);
            return [];
        }
        foreach ($providers as $provider) {
            $form = $provider->getForm($record);
            if (!$form) {
                continue;
            }
            $provider->postProcessDataStructure($record, $dataStructArray, $identifier);
            if ($form->getOption(Form::OPTION_STATIC)) {
                // This provider has requested static DS caching; stop attempting
                // to process any other DS, cache and return this DS as final result:
                $this->configurationService->setInCaches($dataStructArray, true, $identifier);
                return $dataStructArray;
            }
        }
        if (empty($dataStructArray)) {
            $dataStructArray = ['ROOT' => ['el' => []]];
        }

        $dataStructArray = $this->patchTceformsWrapper($dataStructArray);
        $this->configurationService->setInCaches($dataStructArray, false, $identifier);

        return $dataStructArray;
    }

    /**
     * Temporary method during FormEngine transition!
     *
     * Performs a duplication in data source, applying a wrapper
     * around field configurations which require it for correct
     * rendering in flex form containers.
     *
     * @param array $dataStructure
     * @param null|string $parentIndex
     * @return array
     */
    protected function patchTceformsWrapper(array $dataStructure, $parentIndex = null)
    {
        foreach ($dataStructure as $index => $subStructure) {
            if (is_array($subStructure)) {
                $dataStructure[$index] = $this->patchTceformsWrapper($subStructure, $index);
            }
        }
        if (isset($dataStructure['config']['type']) && $parentIndex !== 'TCEforms') {
            $dataStructure = ['TCEforms' => $dataStructure];
        }
        return $dataStructure;
    }

    /**
     * Method used to ensure that all Closures in the data
     * structure are evaluated. The returned array is then
     * serialisation-safe. Closures can occur whenever Flux
     * fields of certain types are used, for example the
     * "custom" field type (which generates a Closure that
     * evaluates the tag content in a deferred manner).
     *
     * @param array $dataStructureArray
     * @param array $parameters
     * @return array
     */
    protected function recursivelyEvaluateClosures(array $dataStructureArray, array $parameters)
    {
        foreach ($dataStructureArray as $key => $value) {
            if ($value instanceof \Closure) {
                $dataStructureArray[$key] = $value($parameters);
            } elseif (is_array($value)) {
                $dataStructureArray[$key] = $this->recursivelyEvaluateClosures($value, $parameters);
            }
        }
        return $dataStructureArray;
    }

    /**
     * @return VariableFrontend
     * @codeCoverageIgnore
     */
    protected function getCache()
    {
        static $cache;
        if (!$cache) {
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('flux');
        }
        return $cache;
    }

    /**
     * @return VariableFrontend
     * @codeCoverageIgnore
     */
    protected function getRuntimeCache()
    {
        static $cache;
        if (!$cache) {
            $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('cache_runtime');
        }
        return $cache;
    }
}
