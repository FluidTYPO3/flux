<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class WizardItems implements NewContentElementWizardHookInterface
{
    protected FluxService $configurationService;
    protected WorkspacesAwareRecordService $recordService;

    public function __construct()
    {
        /** @var FluxService $configurationService */
        $configurationService = GeneralUtility::makeInstance(FluxService::class);
        $this->configurationService = $configurationService;

        /** @var WorkspacesAwareRecordService $recordService */
        $recordService = GeneralUtility::makeInstance(WorkspacesAwareRecordService::class);
        $this->recordService = $recordService;
    }

    /**
     * @param array $items
     * @param NewContentElementController $parentObject
     */
    public function manipulateWizardItems(&$items, &$parentObject): void
    {
        $enabledContentTypes = [];
        $fluidContentTypeNames = [];
        /** @var int $pageUid */
        $pageUid = 0;

        $defaultValues = (array) GeneralUtility::_GET('defVals');
        /** @var array $dataArray */
        $dataArray = $defaultValues['tt_content'] ?? [];
        $pageUidFromUrl = GeneralUtility::_GET('id');
        $pageUidFromUrl = is_scalar($pageUidFromUrl) ? (int) $pageUidFromUrl : 0;
        $pageUidFromDataArray = (int) key($dataArray);

        if ($pageUidFromDataArray > 0) {
            $pageUid = $pageUidFromDataArray;
        } elseif ($pageUidFromUrl > 0) {
            $pageUid = $pageUidFromUrl;
        }

        if ($pageUid === 0) {
            $reflectionProperty = new \ReflectionProperty($parentObject, 'id');
            $reflectionProperty->setAccessible(true);
            $pageUidFroimParentObject = $reflectionProperty->getValue($parentObject);
            $pageUid = is_scalar($pageUidFroimParentObject) ? (int) $pageUidFroimParentObject : 0;
        }
        if ($pageUid > 0) {
            try {
                /** @var SiteFinder $siteFinder */
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                $site = $siteFinder->getSiteByPageId($pageUid);
                $siteConfiguration = $site->getConfiguration();
                $enabledContentTypes = GeneralUtility::trimExplode(
                    ',',
                    $siteConfiguration['flux_content_types'] ?? '',
                    true
                );
            } catch (SiteNotFoundException $exception) {
                // Suppressed; a site not being found is not a fatal error in this context.
            }
        }

        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
        $fluidContentTypeNames = (array) $contentTypeManager->fetchContentTypeNames();
        $items = $this->filterPermittedFluidContentTypesByInsertionPosition($items, $parentObject, $pageUid);
        if (!empty($enabledContentTypes)) {
            foreach ($items as $name => $item) {
                $contentTypeName = $item['tt_content_defValues']['CType'] ?? null;
                if (!empty($contentTypeName)
                    && in_array($contentTypeName, $fluidContentTypeNames, true)
                    && !in_array($contentTypeName, $enabledContentTypes, true)
                ) {
                    unset($items[$name]);
                }
            }
        }
    }

    protected function filterPermittedFluidContentTypesByInsertionPosition(
        array $items,
        NewContentElementController $parentObject,
        int $pageUid
    ): array {
        /** @var int|null $colPos */
        $colPos = GeneralUtility::_GET('colPos');
        if ($colPos === null) {
            $reflectionProperty = new \ReflectionProperty($parentObject, 'colPos');
            $reflectionProperty->setAccessible(true);
            $colPosFromParentObject = $reflectionProperty->getValue($parentObject);
            $colPos = is_scalar($colPosFromParentObject) ? (int) $colPosFromParentObject : 0;
        }
        list ($whitelist, $blacklist) = $this->getWhiteAndBlackListsFromPageAndContentColumn(
            $pageUid,
            (int) $colPos
        );
        $overrides = HookHandler::trigger(
            HookHandler::ALLOWED_CONTENT_RULES_FETCHED,
            [
                'whitelist' => $whitelist,
                'blacklist' => $blacklist,
                'controller' => $parentObject
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
                'controller' => $parentObject,
                'items' => $items
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
        $pageProviders = $this->configurationService->resolveConfigurationProviders('pages', null, $pageRecord);
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
            $contentProviders = $this->configurationService->resolveConfigurationProviders(
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
                        list ($whitelist, $blacklist) = $this->appendToWhiteAndBlacklistFromComponent(
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
            if (false !== strpos($name, '_')) {
                $parts = explode('_', $name);
                array_push($preserveHeaders, reset($parts));
            }
        }
        foreach ($items as $name => $item) {
            if (false === strpos($name, '_') && false === in_array($name, $preserveHeaders)) {
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
                if (false !== strpos($name, '_') && !in_array($item['tt_content_defValues']['CType'], $whitelist)) {
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
        if (null !== $allowed) {
            $whitelist = array_merge($whitelist, GeneralUtility::trimExplode(',', $allowed));
        }
        /** @var string|null $denied */
        $denied = $component->getVariable('deniedContentTypes');
        if (null !== $denied) {
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
