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
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * WizardItemsHookSubscriber
 */
class WizardItems implements NewContentElementWizardHookInterface
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
     * @param WorkspacesAwareRecordService $recordService
     * @return void
     */
    public function injectRecordService(WorkspacesAwareRecordService $recordService)
    {
        $this->recordService = $recordService;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->injectObjectManager($objectManager);
        /** @var FluxService $configurationService */
        $configurationService = $this->objectManager->get(FluxService::class);
        $this->injectConfigurationService($configurationService);
        /** @var WorkspacesAwareRecordService $recordService */
        $recordService = $this->objectManager->get(WorkspacesAwareRecordService::class);
        $this->injectRecordService($recordService);
    }

    /**
     * @param array $items
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController
     * @return void
     */
    public function manipulateWizardItems(&$items, &$parentObject)
    {
        $enabledContentTypes = [];
        $fluidContentTypeNames = [];
        $pageUid = 0;
        if (class_exists(SiteFinder::class)) {
            $dataArray = GeneralUtility::_GET('defVals')['tt_content'] ?? [];
            $pageUid = (int) (key($dataArray) ?? GeneralUtility::_GET('id') ?? ObjectAccess::getProperty($parentObject, 'id', true));
            if ($pageUid > 0) {
                try {
                    $enabledContentTypes = [];
                    $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                    $site = $siteFinder->getSiteByPageId($pageUid);
                    $siteConfiguration = $site->getConfiguration();
                    if (!empty($siteConfiguration['flux_content_types'])) {
                        $enabledContentTypes = GeneralUtility::trimExplode(',', $siteConfiguration['flux_content_types'] ?? '', true);
                    }
                } catch (SiteNotFoundException $exception) {
                    // Suppressed; a site not being found is not a fatal error in this context.
                }
            }
        }

        $fluidContentTypeNames = GeneralUtility::makeInstance(ContentTypeManager::class)->fetchContentTypeNames();
        $items = $this->filterPermittedFluidContentTypesByInsertionPosition($items, $parentObject, $pageUid);
        if (!empty($enabledContentTypes)) {
            foreach ($items as $name => $item) {
                $contentTypeName = $item['tt_content_defValues']['CType'] ?? null;
                if (!empty($contentTypeName) && in_array($contentTypeName, $fluidContentTypeNames, true) && !in_array($contentTypeName, $enabledContentTypes, true)) {
                    unset($items[$name]);
                }
            }
        }
    }

    protected function filterPermittedFluidContentTypesByInsertionPosition(array $items, NewContentElementController $parentObject, int $pageUid): array
    {
        list ($whitelist, $blacklist) = $this->getWhiteAndBlackListsFromPageAndContentColumn(
            $pageUid,
            (int) ($dataArray['colPos'] ?? GeneralUtility::_GET('colPos') ?? ObjectAccess::getProperty($parentObject, 'colPos', true))
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

    /**
     * @param integer $pageUid
     * @param integer $columnPosition
     * @param integer $relativeUid
     * @return array
     */
    protected function getWhiteAndBlackListsFromPageAndContentColumn($pageUid, $columnPosition)
    {
        $whitelist = [];
        $blacklist = [];
        // if a Provider is registered for the "pages" table, try to get a Grid from it. If the Grid
        // returned contains a Column which matches the desired colPos value, attempt to read a list
        // of allowed/denied content element types from it.
        $pageRecord = (array) $this->recordService->getSingle('pages', '*', $pageUid);
        $pageProviders = $this->configurationService->resolveConfigurationProviders('pages', null, $pageRecord);
        $parentRecordUid = ColumnNumberUtility::calculateParentUid($columnPosition);
        $pageColumnPosition = $parentRecordUid > 0 ? $this->findParentColumnPosition($parentRecordUid) : $columnPosition;
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

    /**
     * @param array $providers
     * @param array $record
     * @param array $whitelist
     * @param array $blacklist
     * @param integer $columnPosition
     */
    protected function appendToWhiteAndBlacklistFromProviders(
        array $providers,
        array $record,
        array &$whitelist,
        array &$blacklist,
        $columnPosition
    ) {
        if ($columnPosition >= ColumnNumberUtility::MULTIPLIER) {
            $columnPosition = ColumnNumberUtility::calculateLocalColumnNumber($columnPosition);
        }
        foreach ($providers as $provider) {
            $grid = $provider->getGrid($record);
            if (null === $grid) {
                continue;
            }
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

    /**
     * @param array $items
     * @return array
     */
    protected function trimItems(array $items)
    {
        $preserveHeaders = [];
        foreach ($items as $name => $item) {
            if (false !== strpos($name, '_')) {
                array_push($preserveHeaders, reset(explode('_', $name)));
            }
        }
        foreach ($items as $name => $item) {
            if (false === strpos($name, '_') && false === in_array($name, $preserveHeaders)) {
                unset($items[$name]);
            }
        }
        return $items;
    }

    /**
     * @param array $items
     * @param array $blacklist
     * @return array
     */
    protected function applyBlacklist(array $items, array $blacklist)
    {
        $blacklist = array_unique($blacklist);
        if (0 < count($blacklist)) {
            foreach ($blacklist as $contentElementType) {
                foreach ($items as $name => $item) {
                    if ($item['tt_content_defValues']['CType'] === $contentElementType) {
                        unset($items[$name]);
                    }
                }
            }
        }
        return $items;
    }

    /**
     * @param array $items
     * @param array $whitelist
     * @return array
     */
    protected function applyWhitelist(array $items, array $whitelist)
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

    /**
     * @param FormInterface $component
     * @param array $whitelist
     * @param array $blacklist
     * @return array
     */
    protected function appendToWhiteAndBlacklistFromComponent(
        FormInterface $component,
        array $whitelist,
        array $blacklist
    ) {
        $allowed = $component->getVariable('allowedContentTypes');
        if (null !== $allowed) {
            $whitelist = array_merge($whitelist, GeneralUtility::trimExplode(',', $allowed));
        }
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
