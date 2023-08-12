<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WizardItemsManipulator
{
    protected ProviderResolver $providerResolver;
    protected WorkspacesAwareRecordService $recordService;
    protected ContentTypeManager $contentTypeManager;
    protected SiteFinder $siteFinder;

    public function __construct(
        ProviderResolver $providerResolver,
        WorkspacesAwareRecordService $recordService,
        ContentTypeManager $contentTypeManager,
        SiteFinder $siteFinder
    ) {
        $this->providerResolver = $providerResolver;
        $this->recordService = $recordService;
        $this->contentTypeManager = $contentTypeManager;
        $this->siteFinder = $siteFinder;
    }

    public function manipulateWizardItems(array $items, int $pageUid, ?int $columnPosition): array
    {
        try {
            $site = $this->siteFinder->getSiteByPageId($pageUid);
            $siteConfiguration = $site->getConfiguration();
            $enabledContentTypes = GeneralUtility::trimExplode(
                ',',
                $siteConfiguration['flux_content_types'] ?? '',
                true
            );
            if (!empty($enabledContentTypes)) {
                $fluidContentTypeNames = (array) $this->contentTypeManager->fetchContentTypeNames();
                $items = array_filter(
                    $items,
                    function (array $item) use ($enabledContentTypes) {
                        return in_array($item['tt_content_defValues']['CType'], $enabledContentTypes, true);
                    }
                );
            }
        } catch (SiteNotFoundException $exception) {
            // Suppressed; a site not being found is not a fatal error in this context.
        }

        if ($columnPosition !== null) {
            $items = $this->filterPermittedFluidContentTypesByInsertionPosition($items, $pageUid, $columnPosition);
        }

        return $items;
    }

    protected function filterPermittedFluidContentTypesByInsertionPosition(
        array $items,
        int $pageUid,
        int $columnPosition
    ): array {
        [$whitelist, $blacklist] = $this->getWhiteAndBlackListsFromPageAndContentColumn($pageUid, $columnPosition);
        $overrides = HookHandler::trigger(
            HookHandler::ALLOWED_CONTENT_RULES_FETCHED,
            [
                'whitelist' => $whitelist,
                'blacklist' => $blacklist,
                'pageUid' => $pageUid,
                'columnPosition' => $columnPosition,
            ]
        );

        $whitelist = $overrides['whitelist'];
        $blacklist = $overrides['blacklist'];

        $items = $this->applyWhitelist($items, $whitelist);
        $items = $this->applyBlacklist($items, $blacklist);
        $items = $this->trimItems($items);
        return HookHandler::trigger(
            HookHandler::ALLOWED_CONTENT_FILTERED,
            [
                'whitelist' => $whitelist,
                'blacklist' => $blacklist,
                'items' => $items,
                'pageUid' => $pageUid,
                'columnPosition' => $columnPosition,
            ]
        )['items'];
    }

    protected function getWhiteAndBlackListsFromPageAndContentColumn(int $pageUid, int $columnPosition): array
    {
        $whitelist = [];
        $blacklist = [];
        // if a Provider is registered for the "pages" table, try to get a Grid from it. If the Grid
        // returned contains a Column which matches the desired colPos value, attempt to read a list
        // of allowed/denied content element types from it.
        $pageRecord = (array) $this->recordService->getSingle('pages', '*', $pageUid);
        $pageProviders = $this->providerResolver->resolveConfigurationProviders('pages', null, $pageRecord);
        $parentRecordUid = ColumnNumberUtility::calculateParentUid($columnPosition);
        $pageColumnPosition = $parentRecordUid > 0
            ? $this->findParentColumnPosition($parentRecordUid)
            : $columnPosition;
        $this->appendToWhiteAndBlacklistFromProviders(
            $pageProviders,
            $pageRecord,
            $whitelist,
            $blacklist,
            $pageColumnPosition
        );

        if ($parentRecordUid === 0) {
            return [$whitelist, $blacklist];
        }

        // if these variables now indicate that we are inserting content elements into a Flux-enabled content
        // area inside another content element, attempt to read allowed/denied content types from the
        // Grid returned by the Provider that applies to the parent element's type and configuration
        // (admitted, that's quite a mouthful - but it's not that different from reading the values from
        // a page template like above; it's the same principle).
        if ($parentRecordUid > 0) {
            $parentRecord = (array) $this->recordService->getSingle('tt_content', '*', $parentRecordUid);
            $contentProviders = $this->providerResolver->resolveConfigurationProviders(
                'tt_content',
                null,
                $parentRecord
            );
            $this->appendToWhiteAndBlacklistFromProviders(
                $contentProviders,
                $parentRecord,
                $whitelist,
                $blacklist,
                $columnPosition
            );
        }
        // White/blacklist filtering. If whitelist contains elements, filter the list
        // of possible types by whitelist first. Then apply the blacklist, removing
        // any element types recorded herein.
        $whitelist = array_unique($whitelist);
        $blacklist = array_unique($blacklist);
        return [$whitelist, $blacklist];
    }

    protected function appendToWhiteAndBlacklistFromProviders(
        array $providers,
        array $record,
        array &$whitelist,
        array &$blacklist,
        int $columnPosition
    ): void {
        if ($columnPosition >= ColumnNumberUtility::MULTIPLIER) {
            $columnPosition = ColumnNumberUtility::calculateLocalColumnNumber($columnPosition);
        }
        foreach ($providers as $provider) {
            $grid = $provider->getGrid($record);
            foreach ($grid->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    if ($column->getColumnPosition() === $columnPosition) {
                        [$whitelist, $blacklist] = $this->appendToWhiteAndBlacklistFromComponent(
                            $column,
                            $whitelist,
                            $blacklist
                        );
                    }
                }
            }
        }
    }

    protected function trimItems(array $items): array
    {
        $preserveHeaders = [];
        foreach ($items as $name => $item) {
            if (strpos($name, '_') !== false) {
                $parts = explode('_', $name);
                $preserveHeaders[] = reset($parts);
            }
        }
        foreach ($items as $name => $item) {
            if (strpos($name, '_') === false && !in_array($name, $preserveHeaders, true)) {
                unset($items[$name]);
            }
        }
        return $items;
    }

    protected function applyBlacklist(array $items, array $blacklist): array
    {
        $blacklist = array_unique($blacklist);
        if (0 < count($blacklist)) {
            foreach ($blacklist as $contentElementType) {
                foreach ($items as $name => $item) {
                    if (($item['tt_content_defValues']['CType'] ?? null) === $contentElementType) {
                        unset($items[$name]);
                    }
                }
            }
        }
        return $items;
    }

    protected function applyWhitelist(array $items, array $whitelist): array
    {
        $whitelist = array_unique($whitelist);
        if (0 < count($whitelist)) {
            foreach ($items as $name => $item) {
                $contentType = $item['tt_content_defValues']['CType'] ?? '';
                if (strpos($name, '_') !== false && !in_array($contentType, $whitelist, true)) {
                    unset($items[$name]);
                }
            }
        }
        return $items;
    }

    protected function appendToWhiteAndBlacklistFromComponent(
        FormInterface $component,
        array $whitelist,
        array $blacklist
    ): array {
        /** @var string|null $allowed */
        $allowed = $component->getVariable('allowedContentTypes');
        if ($allowed !== null) {
            // Whitelist is not merged - it is mutually exclusive. If defined in content element's nested column, then
            // that whitelist will be the only whitelist considered. Otherwise, the page column's whitelist is used.
            $whitelist = GeneralUtility::trimExplode(',', $allowed);
        }
        /** @var string|null $denied */
        $denied = $component->getVariable('deniedContentTypes');
        if ($denied !== null) {
            // But the blacklist is cumulative: if the content element's nested column defines unwanted content types
            // and the page column also defines unwanted content types then the two lists are combined into one larger
            // list of unwanted content types declared by both the page and parent content column.
            $blacklist = array_merge($blacklist, GeneralUtility::trimExplode(',', $denied));
        }
        return [$whitelist, $blacklist];
    }

    protected function findParentColumnPosition(int $parentRecordUid): int
    {
        $parentRecord = (array) $this->recordService->getSingle('tt_content', 'uid,colPos', $parentRecordUid);
        return $parentRecord['colPos'] >= ColumnNumberUtility::MULTIPLIER
            ? $this->findParentColumnPosition(ColumnNumberUtility::calculateParentUid($parentRecord['colPos']))
            : (int) $parentRecord['colPos'];
    }
}
