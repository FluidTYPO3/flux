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

        try {
            $all = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
            );
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 1700841298) {
                throw $exception;
            }
            // This will happen only on v13 and only when Flux is being triggered in uncached contexts (basically,
            // INT cObject types). The reason is that TS is not available in such contexts through ServerRequest.
            // This imposes a set of limitations on Flux in such contexts:
            // - Any and all TypoScript settings will not be available.
            // - This includes view path configurations - only default paths and/or paths specified by the Provider
            //   will be available when Flux tries to render things.
            // This affects several features of Flux, such as the ability to override Form details through TypoScript.
            // The affected features *ARE* relatively rarely used, but there's no reasonable way around it. The only
            // way that this *could* have been patched would be to reproduce all of the code from
            // TYPO3\CMS\Frontend\Middleware\PrepareTypoScriptFrontendRendering, which involves from-scratch attempting
            // to read, compile and finally parse every single TypoScript template throughout the root line.
            // A decision has been made that this is so excessive it will not be attempted. So, sorry, but if your use
            // case demands that you MUST have TypoScript available in your Flux context in uncached contexts on v13+,
            // your only option will be to replace this class and override this method, filling it with your preferred
            // way of reading TypoScript when it cannot be read from the ServerRequest "frontend.typoscript" attribute.
            $all = [];
        }

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
