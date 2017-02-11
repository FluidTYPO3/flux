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
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Dynamic FlexForm insertion hook class
 */
class DynamicFlexForm
{

    const OPTION_NEEDS_TCEFORMS_WRAPPER = 'needsTceformsWrapper';

    /**
     * @var array
     */
    protected static $generatedDataSources = [];

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
        return [
            'type' => 'flux',
            'tableName' => $tableName,
            'fieldName' => $fieldName,
            'record' => $record
        ];
    }

    /**
     * @param array $identifier
     * @return array
     */
    protected function resolveDataStructureByIdentifier(array $identifier)
    {
        $record = $identifier['record'];
        if (!$record) {
            return [];
        }
        $fieldName = $identifier['fieldName'];
        $dataStructArray = [];
        $providers = $this->configurationService->resolveConfigurationProviders($tableName, $fieldName, $record);
        if (count($providers) === 0) {
            return [];
        }
        foreach ($providers as $provider) {
            $form = $provider->getForm($record);
            if (!$form) {
                continue;
            }
            $formId = $form->getId();
            if ($form->getOption(Form::OPTION_STATIC)) {
                $cache = $this->getCache();
                $cacheKey = $this->calculateFormCacheKey($formId);
                if ($cache->has($cacheKey)) {
                    return $cache->get($cacheKey);
                } else {
                    // This provider has requested static DS caching; stop attempting
                    // to process any other DS and cache this DS as final result:
                    if (isset(static::$generatedDataSources[$formId])) {
                        // DS has already been generated, skip processing now and refer to existing DS.
                        return $dataStructArray;
                    }
                    $provider->postProcessDataStructure($record, $dataStructArray, $identifier);
                    $cache->set($cacheKey, $dataStructArray);
                    return $dataStructArray;
                }
                return $identifier;
            } else {
                $provider->postProcessDataStructure($record, $dataStructArray, $identifier);
            }
        }
        if (empty($dataStructArray)) {
            $dataStructArray = ['ROOT' => ['el' => []]];
        }

        // Trigger TCEforms dimension patching only if required by TYPO3 version according to CompatibilityRegistry.
        if (CompatibilityRegistry::get(static::OPTION_NEEDS_TCEFORMS_WRAPPER)) {
            $dataStructArray = $this->patchTceformsWrapper($dataStructArray);
        }

        return $dataStructArray;
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
        if (isset($identifier['staticIdentity'])) {
            $dataSourceIdentity = $identifier['staticIdentity'];
        } else {
            $dataSourceIdentity = $identifier['tableName'] . '.' . $identifier['fieldName'] . ':' . $identifier['record']['uid'];
        }
        if (isset(static::$generatedDataSources[$dataSourceIdentity])) {
            return static::$generatedDataSources[$dataSourceIdentity];
        }
        static::$generatedDataSources[$dataSourceIdentity] = $this->resolveDataStructureByIdentifier($identifier);
        return static::$generatedDataSources[$dataSourceIdentity];
    }

    /**
     * Hook for generating dynamic FlexForm source code.
     *
     * NOTE: patches data structure resolving in a way that solves
     * a regression in the TYPO3 core when dealing with IRRE AJAX
     * requests (in which the database record is no longer fetched
     * by the controller). This patches not only data structure
     * resolving for Flux data structures but indeed any data
     * structure built using hooks or involving user functions which
     * require the entire record (but when using hooks, supports
     * only extensions which are loaded AFTER or depend on Flux).
     *
     * @param array $dataStructArray
     * @param array $conf
     * @param array $row
     * @param string $table
     * @param string $fieldName
     * @return void
     * @codingStandardsIgnoreStart this method signature is excluded from CGL checks due to required "bad" method name
     */
    public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, &$row, $table, $fieldName)
    {
        // @codingStandardsIgnoreEnd This comment ends CGL exemption to ensure only the signature is exempted!
        $cache = $this->getCache();
        if (empty($fieldName) === true) {
            // Cast NULL if an empty but not-NULL field name was passed. This has significance to the Flux internals in
            // respect to which ConfigurationProvider(s) are returned.
            $fieldName = null;
        }
        if (!empty($fieldName) && !isset($row[$fieldName])) {
            // Patch required (possibly temporary). Due to changes in TYPO3 in the new FormEngine we must fetch the
            // database record at this point when the record is incomplete, which happens when attempting to render
            // IRRE records. The reason is that the controller that creates the HTML does not fetch the record any
            // more - and that the AJAX request contains only the UID. So, we fetch the record here to ensure it
            // contains the necessary fields. DOES NOT WORK FOR NEW RECORDS - SEE COMMENTS BELOW.
            $row = $this->recordService->getSingle($table, '*', $row['uid']);
        }
        $defaultDataSourceCacheIdentifier = $table . '_' . $fieldName . '_' . sha1(serialize($conf));
        if (!$row) {
            // In the case that the database record cannot be fetched we are dealing with a new or otherwise deleted
            // or unidentifiable record. This happens primarily when AJAX requests are made to render IRRE records
            // without the parent record having been saved first. To accommodate this case we have to be slightly
            // creative and store a "default" data source definition which is identified based on a checksum of the
            // configuration provided. Whenever we are then unable to fetch a record, we can at least attempt to
            // locate a default data source in previously cached content. NB: we enforce a VERY high cache lifetime
            // and continually refresh it every time it is possible to render a new DS that can serve as default.
            $dataStructArray = (array) $cache->get($defaultDataSourceCacheIdentifier);
        } else {
            if (false === is_array($dataStructArray)) {
                $dataStructArray = [];
            }
            $providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $row);
            foreach ($providers as $provider) {
                $form = $provider->getForm($row);
                if (!$form) {
                    continue;
                }
                $formId = $form->getId();
                if ($form->getOption(Form::OPTION_STATIC)) {

                    $cacheKey = $this->calculateFormCacheKey($formId);
                    if ($cache->has($cacheKey)) {
                        $dataStructArray = $cache->get($cacheKey);
                        return;
                    }
                    // This provider has requested static DS caching; stop attempting
                    // to process any other DS and cache this DS as final result:
                    if (isset(static::$generatedDataSources[$formId])) {
                        // DS has already been generated, skip processing now and refer to existing DS.
                        return;
                    }
                    $provider->postProcessDataStructure($row, $dataStructArray, $conf);
                    $cache->set($cacheKey, $dataStructArray);
                    return;
                } else {
                    $provider->postProcessDataStructure($row, $dataStructArray, $conf);
                }
            }
            if (empty($dataStructArray)) {
                $dataStructArray = ['ROOT' => ['el' => []]];
            }
            $evaluationParameters = [];
            $cache->set(
                $defaultDataSourceCacheIdentifier,
                $this->recursivelyEvaluateClosures($dataStructArray, $evaluationParameters),
                [],
                (time() + 31536000)
            );
        }

        // Trigger TCEforms dimension patching only if required by TYPO3 version according to CompatibilityRegistry.
        if (CompatibilityRegistry::get(static::OPTION_NEEDS_TCEFORMS_WRAPPER)) {
            $dataStructArray = $this->patchTceformsWrapper($dataStructArray);
        }
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
     * @param string $formId
     *
     * @return string
     */
    private function calculateFormCacheKey($formId)
    {
        return 'datastructure-' . $formId;
    }
}
