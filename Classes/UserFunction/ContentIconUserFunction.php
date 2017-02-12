<?php
namespace FluidTYPO3\Flux\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Renders content type icons.
 */
class ContentIconUserFunction
{

    /**
     * @param array $parameters
     * @return string
     */
    public function getIcon(array $parameters)
    {
        $record = $parameters['row'];
        if (empty($record)) {
            // fast skip; this can happen for custom template-as-CType registrations. Icon delivered later in runtime.
            return;
        }

        if (isset($GLOBALS['TCA']['tt_content']['typeicon_classes'][$record['CType']])) {
            return $GLOBALS['TCA']['tt_content']['typeicon_classes'][$record['CType']];
        }

        $cacheId = 'content_icon_' . $record['CType'] . '_' . $record['uid'];

        $iconIdentifierFromCache = $this->getIconFromCache($cacheId);
        if ($iconIdentifierFromCache) {
            return $iconIdentifierFromCache;
        }

        $field = $this->detectFirstFlexTypeFieldInTableFromPossibilities('tt_content', array_keys($record));
        $provider = $this->getProviderResolver()->resolvePrimaryConfigurationProvider('tt_content', $field, $record);
        if (!$provider) {
            return '';
        }

        $form = $provider->getForm($parameters['row']);
        if (!$form) {
            return '';
        }

        $icon = MiscellaneousUtility::getIconForTemplate($form);
        if (0 === strpos($icon, 'EXT:')) {
            $icon = GeneralUtility::getFileAbsFileName($icon);
        } elseif ('/' === $icon[0]) {
            $icon = rtrim(PATH_site, '/') . $icon;
        }
        $iconIdentifier = null;
        if (!file_exists($icon)) {
            return 'apps-pagetree-root';
        }
        $extension = pathinfo($icon, PATHINFO_EXTENSION);
        switch (strtolower($extension)) {
            case 'svg':
            case 'svgz':
                $iconProvider = SvgIconProvider::class;
                break;
            default:
                $iconProvider = BitmapIconProvider::class;
        }
        $iconIdentifier = 'icon-flux-' . $form->getId();
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon($iconIdentifier, $iconProvider, ['source' => $icon]);
        $this->storeIconMetadataInCache(
            $cacheId,
            [
                'identifier' => $iconIdentifier,
                'provider' => $iconProvider,
                'configuration' => [
                    'source' => $icon
                ]
            ]
        );
        return $iconIdentifier;
    }

    /**
     * @param string $identifier
     * @return string
     */
    protected function getIconFromCache($identifier)
    {
        $entry = $this->getCache()->get($identifier);
        if (!$entry) {
            return '';
        }
        $iconIdentifier = $entry['identifier'];
        if (!empty($entry['provider'])) {
            $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
            $iconRegistry->registerIcon($iconIdentifier, $entry['provider'], $entry['configuration']);
        }
        return $iconIdentifier;
    }

    /**
     * @param string $table
     * @param array $fields
     * @return string
     */
    protected function detectFirstFlexTypeFieldInTableFromPossibilities($table, $fields)
    {
        foreach ($fields as $fieldName) {
            if ('flex' === $GLOBALS['TCA'][$table]['columns'][$fieldName]['config']['type']) {
                return $fieldName;
            }
        }
        return null;
    }

    /**
     * @param string $identifier
     * @param array $iconMetadata
     */
    protected function storeIconMetadataInCache($identifier, array $iconMetadata)
    {
        $this->getCache()->set($identifier, $iconMetadata);
    }

    /**
     * @return VariableFrontend
     */
    protected function getCache()
    {
        $objectManager = $this->getObjectManager();
        return $objectManager->get(CacheManager::class, $objectManager)->getCache('flux');
    }

    /**
     * @return ProviderResolver
     */
    protected function getProviderResolver()
    {
        return $this->getObjectManager()->get(ProviderResolver::class);
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

}
