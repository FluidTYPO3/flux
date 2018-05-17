<?php
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController;
use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * WizardItemsHookSubscriber
 */
class WizardItemsHookSubscriber implements NewContentElementWizardHookInterface
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
        $items = $this->filterPermittedFluidContentTypesByInsertionPosition($items, $parentObject);
    }

    /**
     * @param array $items
     * @param NewContentElementController $parentObject
     * @return array
     */
    protected function filterPermittedFluidContentTypesByInsertionPosition(array $items, $parentObject)
    {
        list ($whitelist, $blacklist) = $this->getWhiteAndBlackListsFromPageAndContentColumn(
            $parentObject->id,
            $parentObject->colPos,
            $parentObject->uid_pid
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
        $items = $this->applyDefaultValues($items, $this->getDefaultValues());
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
     * @return array
     */
    protected function getDefaultValues()
    {
        $values = GeneralUtility::_GET('defVals');
        return (array) $values['tt_content'];
    }

    /**
     * @param integer $pageUid
     * @param integer $columnPosition
     * @param integer $relativeUid
     * @return array
     */
    protected function getWhiteAndBlackListsFromPageAndContentColumn($pageUid, $columnPosition, $relativeUid)
    {
        $whitelist = [];
        $blacklist = [];
        // if a Provider is registered for the "pages" table, try to get a Grid from it. If the Grid
        // returned contains a Column which matches the desired colPos value, attempt to read a list
        // of allowed/denied content element types from it.
        $pageRecord = (array) $this->recordService->getSingle('pages', '*', $pageUid);
        $pageProviders = $this->configurationService->resolveConfigurationProviders('pages', null, $pageRecord);
        $this->appendToWhiteAndBlacklistFromProviders(
            $pageProviders,
            $pageRecord,
            $whitelist,
            $blacklist,
            $columnPosition
        );
        // Detect what was clicked in order to create the new content element; decide restrictions
        // based on this. Returned parent UID and area name is either non-zero and string, or zero
        // and NULL when record is NOT inserted as child.
        list ($parentRecordUid) = $this->getAreaNameAndParentFromRelativeRecordOrDefaults($relativeUid);
        // if these variables now indicate that we are inserting content elements into a Flux-enabled content
        // area inside another content element, attempt to read allowed/denied content types from the
        // Grid returned by the Provider that applies to the parent element's type and configuration
        // (admitted, that's quite a mouthful - but it's not that different from reading the values from
        // a page template like above; it's the same principle).
        if (0 < $parentRecordUid) {
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
     * @param integer $relativeUid
     * @return array
     */
    protected function getAreaNameAndParentFromRelativeRecordOrDefaults($relativeUid)
    {
        $fluxAreaName = null;
        $parentRecordUid = 0;
        $defaultValues = $this->getDefaultValues();
        if (0 > $relativeUid) {
            // pasting after another element means we should try to resolve the Flux content relation
            // from that element instead of GET parameters (clicked: "create new" icon after other element)
            $parentRecord = $this->recordService->getSingle('tt_content', '*', abs($relativeUid));
            $parentRecordUid = (integer) $parentRecord['tx_flux_parent'];
        } else {
            // attempt to read the target Flux content area from GET parameters (clicked: "create new" icon
            // in top of nested Flux content area
            $parentRecordUid = (integer) $defaultValues['tx_flux_parent'];
        }
        return [$parentRecordUid];
    }

    /**
     * @param array $providers
     * @param array $record
     * @param array $whitelist
     * @param array $blacklist
     * @param integer $columnPosition
     * @param string $fluxAreaName
     */
    protected function appendToWhiteAndBlacklistFromProviders(
        array $providers,
        array $record,
        array &$whitelist,
        array &$blacklist,
        $columnPosition,
        $fluxAreaName = null
    ) {
        foreach ($providers as $provider) {
            $grid = $provider->getGrid($record);
            if (null === $grid) {
                continue;
            }
            foreach ($grid->getRows() as $row) {
                foreach ($row->getColumns() as $column) {
                    if (false === empty($fluxAreaName)) {
                        if ($column->getName() === $fluxAreaName) {
                            list ($whitelist, $blacklist) = $this->appendToWhiteAndBlacklistFromComponent(
                                $column,
                                $whitelist,
                                $blacklist
                            );
                        }
                    } elseif ($column->getColumnPosition() === $columnPosition) {
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
     * @param array $defaultValues
     * @return array
     */
    protected function applyDefaultValues(array $items, array $defaultValues)
    {
        $columnName = rawurlencode($defaultValues['tx_flux_column']);
        $parentUid = (int) $defaultValues['tx_flux_parent'];
        foreach ($items as $name => $item) {
            if (strpos($name, '_') === false) {
                // Skip header columns, identifiable by not having an underscore in name
                continue;
            }
            if (!is_array($items[$name]['tt_content_defValues'] ?? null)) {
                $items[$name]['tt_content_defValues'] = [];
            }
            if (!empty($columnName) && !empty($parentUid)) {
                $items[$name]['tt_content_defValues']['tx_flux_column'] = $columnName;
                $items[$name]['tt_content_defValues']['tx_flux_parent'] = $parentUid;
                $items[$name]['params'] .= '&defVals[tt_content][tx_flux_column]=' . $columnName;
                $items[$name]['params'] .= '&defVals[tt_content][tx_flux_parent]=' . $parentUid;
            }
        }
        return $items;
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
}
