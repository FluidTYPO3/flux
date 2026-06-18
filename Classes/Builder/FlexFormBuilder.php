<?php
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\PageProvider;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\CacheService;
use FluidTYPO3\Flux\Service\PageService;
use FluidTYPO3\Flux\Utility\RequestResolver;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormBuilder
{
    protected CacheService $cacheService;
    protected ProviderResolver $providerResolver;
    protected PageService $pageService;

    public function __construct(
        CacheService $cacheService,
        ProviderResolver $providerResolver,
        PageService $pageService
    ) {
        $this->cacheService = $cacheService;
        $this->providerResolver = $providerResolver;
        $this->pageService = $pageService;
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
        if (($originalIdentifier['dataStructureKey'] ?? 'default') !== 'default') {
            return [];
        }
        if ((int) ($record['uid'] ?? 0) > 0) {
            // If we are resolving a DS for an identified record, the only thing that matters is the record's UID.
            $limitedRecordData = ['uid' => $record['uid']];
        } else {
            $fields = GeneralUtility::trimExplode(
                ',',
                $GLOBALS['TCA'][$tableName]['ctrl']['useColumnsForDefaultValues'] ?? ''
            );
            if ($tableName === 'pages' && empty($record[PageProvider::FIELD_ACTION_MAIN]) && !empty($record['pid'])) {
                // When working with the "pages" table, template inheritance comes into play. Normally this is handled
                // for tables like tt_content by adding the fields that determine which DS to use, to the list in TCA
                // "useColumnsForDefaultValues" which then becomes part of the DS identifier. However, for the "pages"
                // table these fields may be empty (meaning the template selection is inherited) and since some contexts
                // do not pass the "uid" of the record (thus triggering the condition above) we are left with possibly
                // empty values which result in an unresolvable DS.
                // Therefore, we must load the possibly inherited data via the PageService, and use those resolved
                // template selection values as part of our DS identifier.
                // This is NOT necessary if the input record contains an explicitly selected page layout, hence the
                // added check above before entering this condition block.
                if ((int) $record['pid'] < 0) {
                    // we have uid of sibling, need first not-deleted parent
                    $record['pid'] = $this->loadRecordWithoutRestriction(
                        'pages',
                        (int) abs($record['pid']),
                        'uid',
                        false
                    )['uid'] ?? 0;
                }
                $record = array_merge(
                    $record,
                    $this->pageService->getPageTemplateConfiguration($record['pid'], true) ?? []
                );
            } elseif ($tableName === 'tt_content') {
                $request = RequestResolver::getRequest();
                if (strtolower($request->getMethod()) === 'post') {
                    /** @var array $body */
                    $body = $request->getParsedBody();
                    if (isset($body['recordTypeValue']) && ($body['vanillaUid'] ?? 0) < 0) {
                        // Special case: we're adding a new content element and after another content element. This
                        // causes TYPO3 to read the CType value of the *neighbor* content record and pass it as CType
                        // in the $identifier "record" property which holds a virtual record. This in turn causes Flux
                        // to resolve an incorrect DS (the one belonging to the related record's type, not the new
                        // record's type).
                        // Normally this doesn't cause any trouble because FormEngine handles this special case, but
                        // logic which is called via AJAX for handling fields in the DS (for example: section objects)
                        // will receive the incorrect DS which triggers errors if there are fields in the *current,
                        // new* record's DS that aren't in the *neighbor* record's DS.
                        // All of this boils down to TYPO3 declaring "CType" as part of the
                        // "useColumnsForDefaultValues" TCA instruction for tt_content. Values of fields mentioned in
                        // this instruction are copied from the neigbor record to the virtual record mentioned above.
                        // Which is good, when it comes to things like colPos or language - but really bad when it
                        // comes to "CType" which changes the record UI composition.
                        $record['CType'] = $body['recordTypeValue'];
                    }
                }
            }

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
        $provider = $this->providerResolver->resolvePrimaryConfigurationProvider($tableName, $fieldName, $record);
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
        if (($identifier['type'] ?? null) !== 'flux') {
            return [];
        }
        $record = $identifier['record'] ?? null;
        if (!$record) {
            return [];
        }

        $cacheKey = md5(serialize($identifier));

        /** @var array|null $fromCache */
        $fromCache = $this->cacheService->getFromCaches($cacheKey);
        if ($fromCache) {
            return $fromCache;
        }
        if (count($record) === 1 && isset($record['uid']) && is_numeric($record['uid'])) {
            // The record is a stub, has only "uid" and "uid" is numeric. Reload the full record from DB.
            $record = $this->loadRecordWithoutRestriction($identifier['tableName'], (int) $record['uid']);
        }
        if (empty($record)) {
            throw new \UnexpectedValueException('Unable to resolve record for DS processing', 1668011937);
        }
        $fieldName = $identifier['fieldName'];
        $dataStructArray = [];
        $provider = $this->providerResolver->resolvePrimaryConfigurationProvider(
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

        $form = $provider->getForm($record, $fieldName);
        $provider->postProcessDataStructure($record, $dataStructArray, $identifier);

        if (empty($dataStructArray)) {
            $dataStructArray = ['ROOT' => ['el' => []]];
        }

        if ($form && $form->getOption(FormOption::STATIC)) {
            // This provider has requested static DS caching; stop attempting
            // to process any other DS, cache and return this DS as final result:
            $this->cacheService->setInCaches($dataStructArray, true, $cacheKey);
            return $dataStructArray;
        }

        return $dataStructArray;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadRecordWithoutRestriction(
        string $table,
        int $uid,
        string $fields = '*',
        bool $includeDeleted = true
    ): ?array {
        return BackendUtility::getRecord($table, $uid, $fields, '', !$includeDeleted);
    }
}
