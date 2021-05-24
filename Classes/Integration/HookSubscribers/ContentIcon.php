<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * Class ContentIconHookSubscriber
 */
class ContentIcon
{
    /**
     * @var array
     */
    protected $templates = [
        'gridToggle' => '</div><div class="fluidcontent-toggler">
                            <div class="btn-group btn-group-sm" role="group">
                            <a class="btn btn-default %s" title="%s" data-toggler-uid="%s">%s</a> 
                        </div></div><div>'
    ];

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var FluxService
     */
    protected $fluxService;

    /**
     * @var VariableFrontend
     */
    protected $cache;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param FluxService $fluxService
     * @return void
     */
    public function injectFluxService(FluxService $fluxService)
    {
        $this->fluxService = $fluxService;
    }

    /**
     * Construct
     */
    public function __construct()
    {
        $this->injectObjectManager(GeneralUtility::makeInstance(ObjectManager::class));
        $this->injectFluxService($this->objectManager->get(FluxService::class));
        $this->cache = $this->objectManager->get(CacheManager::class)->getCache('flux');
    }

    /**
     * @param array $parameters
     * @param PageLayoutView|DatabaseRecordList $caller
     * @return string
     */
    public function addSubIcon(array $parameters, $caller = null)
    {
        if (!($caller instanceof PageLayoutView || $caller instanceof GridColumnItem)) {
            return '';
        }
        list ($table, $uid, $record) = $parameters;
        if ($table !== 'tt_content') {
            return '';
        }

        $provider = null;
        $icon = '';
        $record = null === $record && 0 < $uid ? BackendUtility::getRecord($table, $uid) : $record;
        $cacheIdentity = $table . $uid . sha1(serialize($record)) . ($this->isRowCollapsed($record) ? 'collapsed' : 'expanded');
        // filter 1: icon must not already be cached and both record and caller must be provided.
        // we check the cache here because at this point, the cache key is decidedly
        // unique and we have not yet consulted the (potentially costly) Provider.
        $cachedIconIdentifier = $this->cache->get($cacheIdentity);
        if ($cachedIconIdentifier !== false) {
            // both empty string and non-empty value means icon was generated and cached, we return
            // the result directly in both such cases, to prevent attempts to re-resolve provider etc.
            return $cachedIconIdentifier;
        } elseif ($record) {
            $field = $this->detectFirstFlexTypeFieldInTableFromPossibilities($table, array_keys($record));
            // filter 2: table must have one field defined as "flex" and record must include it.
            if ($field && array_key_exists($field, $record)) {
                $provider = $this->fluxService->resolvePrimaryConfigurationProvider(
                    $table,
                    $field,
                    $record,
                    null,
                    GridProviderInterface::class
                );
                // filter 3: a Provider must be resolved for the record.
                if ($provider && $provider->getGrid($record)->hasChildren()) {
                    $icon = $this->drawGridToggle($record);
                }
            }
        }

        $this->cache->set($cacheIdentity, $icon);
        return $icon;
    }

    /**
     * @param array $row
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function drawGridToggle(array $row)
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

        $collapseIcon = $iconFactory->getIcon('actions-view-list-collapse', Icon::SIZE_SMALL)->render();
        $expandIcon = $iconFactory->getIcon('actions-view-list-expand', Icon::SIZE_SMALL)->render();
        $label = $GLOBALS['LANG']->sL('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:toggle_content');
        $icon = $collapseIcon . $expandIcon;

        $rendered = sprintf($this->templates['gridToggle'], $this->isRowCollapsed($row)?  'toggler-expand' : 'toggler-collapse', $label, $row['uid'], $icon);

        return HookHandler::trigger(
            HookHandler::PREVIEW_GRID_TOGGLE_RENDERED,
            [
                'rendered' => $rendered,
                'iconCollapse' => $collapseIcon,
                'iconExpand' => $expandIcon,
                'label' => $label
            ]
        )['rendered'];
    }

    /**
     * @param array $row
     * @return string
     */
    protected function isRowCollapsed(array $row)
    {
        $collapsed = false;
        $cookie = $this->getCookie();
        if (null !== $_COOKIE) {
            $cookie = json_decode(urldecode($cookie));
            $collapsed = in_array($row['uid'], (array) $cookie);
        }
        return HookHandler::trigger(
            HookHandler::PREVIEW_GRID_TOGGLE_STATUS_FETCHED,
            [
                'collapsed' => $collapsed,
                'record' => $row,
                'cookie' => $cookie
            ]
        )['collapsed'];
    }

    /**
     * @return string|NULL
     */
    protected function getCookie()
    {
        return true === isset($_COOKIE['fluxCollapseStates']) ? $_COOKIE['fluxCollapseStates'] : null;
    }

    /**
     * @param string $table
     * @param array $fields
     * @return string
     */
    protected function detectFirstFlexTypeFieldInTableFromPossibilities($table, $fields)
    {
        foreach ($fields as $fieldName) {
            if (($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] ?? null) === 'flex') {
                return $fieldName;
            }
        }
        return null;
    }
}
