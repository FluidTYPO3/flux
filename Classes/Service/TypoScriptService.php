<?php
namespace FluidTYPO3\Flux\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class TypoScriptService implements SingletonInterface
{
    private CacheService $cacheService;
    private ConfigurationManagerInterface $configurationManager;

    public function __construct(CacheService $cacheService, ConfigurationManagerInterface $configurationManager)
    {
        $this->cacheService = $cacheService;
        $this->configurationManager = $configurationManager;
    }

    /**
     * Returns the plugin.tx_extsignature.settings array.
     * Accepts any input extension name type.
     */
    public function getSettingsForExtensionName(string $extensionName): array
    {
        $signature = ExtensionNamingUtility::getExtensionSignature($extensionName);
        return (array) $this->getTypoScriptByPath('plugin.tx_' . $signature . '.settings');
    }

    /**
     * Gets the value/array from global TypoScript by
     * dotted path expression.
     *
     * @return array|mixed
     */
    public function getTypoScriptByPath(string $path)
    {
        $cacheId = md5('ts_' . $path);
        $fromCache = $this->cacheService->getFromCaches($cacheId);
        if ($fromCache) {
            return $fromCache;
        }

        $all = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );

        $value = &$all;
        foreach (explode('.', $path) as $segment) {
            $value = ($value[$segment . '.'] ?? $value[$segment] ?? null);
            if ($value === null) {
                break;
            }
        }
        if (is_array($value)) {
            $value = GeneralUtility::removeDotsFromTS($value);
        }
        $this->cacheService->setInCaches($value, true, $cacheId);
        return $value;
    }

}
