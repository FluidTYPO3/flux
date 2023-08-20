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
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * @deprecated Has no substitute functionality in TYPO3v12. To be removed when TYPO3v12 is minimum requirement.
 */
class ContentIcon
{
    protected array $templates = [
        'gridToggle' => '<div class="fluidcontent-toggler col-auto">
                            <div class="btn-group btn-group-sm" role="group">
                            <a class="btn btn-default %s" title="%s" data-toggler-uid="%s">%s</a> 
                        </div></div>',
        'legacyGridToggle' => '</div><div class="fluidcontent-toggler">
                            <div class="btn-group btn-group-sm" role="group">
                            <a class="btn btn-default %s" title="%s" data-toggler-uid="%s">%s</a>
                        </div></div><div>',
    ];

    protected ProviderResolver $providerResolver;
    protected IconFactory $iconFactory;
    protected FrontendInterface $cache;

    public function __construct(
        ProviderResolver $providerResolver,
        IconFactory $iconFactory,
        CacheManager $cacheManager
    ) {
        $this->providerResolver = $providerResolver;
        $this->iconFactory = $iconFactory;
        $this->cache = $cacheManager->getCache('flux');
    }

    /**
     * @param PageLayoutView|GridColumnItem|DatabaseRecordList $caller
     */
    public function addSubIcon(array $parameters, $caller = null): string
    {
        if (!($caller instanceof PageLayoutView || $caller instanceof GridColumnItem)) {
            return '';
        }
        [$table, $uid, $record] = $parameters;
        if ($table !== 'tt_content') {
            return '';
        }

        $provider = null;
        $iconMarkup = '';
        $record = null === $record && 0 < $uid ? BackendUtility::getRecord($table, $uid) : $record;
        $cacheIdentity = $table
            . $uid
            . sha1(serialize($record))
            . ($this->isRowCollapsed($record) ? 'collapsed' : 'expanded');
        // filter 1: icon must not already be cached and both record and caller must be provided.
        // we check the cache here because at this point, the cache key is decidedly
        // unique and we have not yet consulted the (potentially costly) Provider.
        /** @var string|false|null $cachedIconMarkup */
        $cachedIconMarkup = $this->cache->get($cacheIdentity);
        if ($cachedIconMarkup) {
            // both empty string and non-empty value means icon was generated and cached, we return
            // the result directly in both such cases, to prevent attempts to re-resolve provider etc.
            /** @var string $cachedIconMarkup */
            return $cachedIconMarkup;
        } elseif ($cachedIconMarkup !== '' && $record) {
            $field = $this->detectFirstFlexTypeFieldInTableFromPossibilities($table, array_keys($record));
            // filter 2: table must have one field defined as "flex" and record must include it.
            if ($field && array_key_exists($field, $record)) {
                /** @var GridProviderInterface $provider */
                $provider = $this->providerResolver->resolvePrimaryConfigurationProvider(
                    $table,
                    $field,
                    $record,
                    null,
                    [GridProviderInterface::class]
                );
                // filter 3: a Provider must be resolved for the record.
                if ($provider && $provider->getGrid($record)->hasChildren()) {
                    $iconMarkup = $this->drawGridToggle($record);
                }
            }
        }

        $this->cache->set($cacheIdentity, $iconMarkup);
        return $iconMarkup;
    }

    protected function drawGridToggle(array $row): string
    {
        $collapseIcon = $this->iconFactory->getIcon('actions-view-list-collapse', Icon::SIZE_SMALL)->render();
        $expandIcon = $this->iconFactory->getIcon('actions-view-list-expand', Icon::SIZE_SMALL)->render();
        $label = $this->translate('LLL:EXT:flux/Resources/Private/Language/locallang.xlf:toggle_content');
        $icon = $collapseIcon . $expandIcon;

        $template = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11', '<')
            ? $this->templates['legacyGridToggle']
            : $this->templates['gridToggle'];

        $rendered = sprintf(
            $template,
            $this->isRowCollapsed($row) ?  'toggler-expand' : 'toggler-collapse',
            $label,
            $row['uid'],
            $icon
        );

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

    protected function isRowCollapsed(array $row): string
    {
        $collapsed = false;
        $cookie = $this->getCookie();
        if (null !== $cookie) {
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

    protected function detectFirstFlexTypeFieldInTableFromPossibilities(string $table, array $fields): ?string
    {
        foreach ($fields as $fieldName) {
            if (($GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type'] ?? null) === 'flex') {
                return $fieldName;
            }
        }
        return null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getCookie(): ?string
    {
        return true === isset($_COOKIE['fluxCollapseStates']) ? $_COOKIE['fluxCollapseStates'] : null;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translate(string $label): ?string
    {
        return $GLOBALS['LANG']->sL($label);
    }
}
