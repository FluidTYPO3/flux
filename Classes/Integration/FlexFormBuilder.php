<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormBuilder
{
    protected FluxService $configurationService;

    public function __construct()
    {
        /** @var FluxService $fluxService */
        $fluxService = GeneralUtility::makeInstance(FluxService::class);
        $this->configurationService = $fluxService;
    }

    public function resolveDataStructureIdentifier(
        string $tableName,
        string $fieldName,
        array $record,
        array $originalIdentifier = []
    ): array {
        // Select a limited set of the $record being passed. When the $record is a new record, it will have
        // no UID but will contain a list of default values, in which case we extract a smaller list of
        // values based on the "useColumnsForDefaultValues" TCA control (we mimic the amount of data that
        // would be available via the new content wizard). If the record has a UID we record only the UID.
        // In the latter case we sacrifice some performance (having to reload the record by UID) in order
        // to pass an identifier small enough to be part of GET parameters. This class will then "thaw" the
        // record identified by UID to ensure that for all existing records, Providers receive the FULL data.
        if ((integer) ($record['uid'] ?? 0) > 0) {
            $limitedRecordData = ['uid' => $record['uid']];
        } else {
            $fields = GeneralUtility::trimExplode(
                ',',
                $GLOBALS['TCA'][$tableName]['ctrl']['useColumnsForDefaultValues']
            );
            if ($GLOBALS['TCA'][$tableName]['ctrl']['type'] ?? false) {
                $typeField = $GLOBALS['TCA'][$tableName]['ctrl']['type'];
                $fields[] = $GLOBALS['TCA'][$tableName]['ctrl']['type'];
                if ($GLOBALS['TCA'][$tableName]['ctrl'][$typeField]['subtype_value_field'] ?? false) {
                    $fields[] = $GLOBALS['TCA'][$tableName]['ctrl'][$typeField]['subtype_value_field'];
                }
            }
            $fields = array_combine($fields, $fields);
            $limitedRecordData = array_intersect_key($record, $fields);
            $limitedRecordData[$fieldName] = $record[$fieldName];
        }
        $provider = $this->configurationService->resolvePrimaryConfigurationProvider($tableName, $fieldName, $record);
        if (!$provider) {
            return [];
        }
        return [
            'type' => 'flux',
            'tableName' => $tableName,
            'fieldName' => $fieldName,
            'record' => $limitedRecordData,
            'originalIdentifier' => $originalIdentifier
        ];
    }

    public function parseDataStructureByIdentifier(array $identifier): array
    {
        if ($identifier['type'] !== 'flux') {
            return [];
        }
        $record = $identifier['record'];
        if (!$record) {
            return [];
        }

        /** @var array|null $fromCache */
        $fromCache = $this->configurationService->getFromCaches($identifier);
        if ($fromCache) {
            return $fromCache;
        }
        if (count($record) === 1 && isset($record['uid']) && is_numeric($record['uid'])) {
            // The record is a stub, has only "uid" and "uid" is numeric. Reload the full record from DB.
            $record = $this->loadRecordWithoutRestriction($identifier['tableName'], (integer) $record['uid']);
        }
        if (empty($record)) {
            throw new \UnexpectedValueException('Unable to resolve record for DS processing', 1668011937);
        }
        $fieldName = $identifier['fieldName'];
        $dataStructArray = [];
        $provider = $this->configurationService->resolvePrimaryConfigurationProvider(
            $identifier['tableName'],
            $fieldName,
            $record,
            null,
            [DataStructureProviderInterface::class]
        );
        if (!$provider instanceof FormProviderInterface) {
            // No Providers detected - return empty data structure (reported as invalid DS in backend)
            return [];
        }

        $form = $provider->getForm($record);
        $provider->postProcessDataStructure($record, $dataStructArray, $identifier);
        if ($form && $form->getOption(Form::OPTION_STATIC)) {
            // This provider has requested static DS caching; stop attempting
            // to process any other DS, cache and return this DS as final result:
            $this->configurationService->setInCaches($dataStructArray, true, $identifier);
            return $dataStructArray;
        }

        if (empty($dataStructArray)) {
            $dataStructArray = ['ROOT' => ['el' => []]];
        }

        return $dataStructArray;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadRecordWithoutRestriction(string $table, int $uid): ?array
    {
        return BackendUtility::getRecord($table, $uid, '*', '', false);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getCache(): FrontendInterface
    {
        static $cache;
        if (!$cache) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cache = $cacheManager->getCache('flux');
        }
        return $cache;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getRuntimeCache(): FrontendInterface
    {
        static $cache;
        if (!$cache) {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            $cache = $cacheManager->getCache('runtime');
        }
        return $cache;
    }
}
