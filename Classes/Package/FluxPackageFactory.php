<?php
namespace FluidTYPO3\Flux\Package;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use FluidTYPO3\Flux\FluxPackage;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * Class FluxPackageFactory
 *
 * Creates instances of FluxPackage based on provided
 * extension name. Stores created instances in memory.
 */
abstract class FluxPackageFactory
{

    /**
     * @var FluxPackageInterface[]
     */
    protected static $packages = [];

    /**
     * @var array|NULL
     */
    protected static $overrides = null;

    /**
     * Returns the FluxPackage instance associated with
     * and possibly existing in $qualifiedExtensionName.
     *
     * @param string $qualifiedExtensionName
     * @return FluxPackageInterface
     */
    public static function getPackage($qualifiedExtensionName)
    {
        if (empty($qualifiedExtensionName)) {
            throw new PackageNotFoundException('Package name cannot be empty');
        }
        $extensionKey = ExtensionNamingUtility::getExtensionKey($qualifiedExtensionName);
        if (!ExtensionManagementUtility::isLoaded($extensionKey)) {
            throw new PackageNotFoundException(
                sprintf(
                    'Package name %s (extension key %s) is not loaded',
                    $qualifiedExtensionName,
                    $extensionKey
                )
            );
        }
        if (!array_key_exists($extensionKey, static::$packages)) {
            static::$packages[$extensionKey] = FluxPackage::create($qualifiedExtensionName)->upcast();
        }
        return static::$packages[$extensionKey];
    }

    /**
     * Returns the FluxPackage instance associated with
     * and possibly existing in $qualifiedExtensionName,
     * but falls back to returning the Flux root package
     * if the requested package does not exist.
     *
     * @param string $qualifiedExtensionName
     * @return FluxPackageInterface
     */
    public static function getPackageWithFallback($qualifiedExtensionName)
    {
        try {
            return static::getPackage($qualifiedExtensionName);
        } catch (PackageNotFoundException $error) {
            return static::getPackage('FluidTYPO3.Flux');
        }
    }

    /**
     * @param string $qualifiedExtensionName
     * @return array
     */
    protected function getTypoScriptOverrides($qualifiedExtensionName)
    {
        if (static::$overrides === null) {
            $collected = [];
            $typoScript = GeneralUtility::makeInstance(ObjectManager::class)
                ->get(ObjectManagerInterface::class)
                ->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            foreach ((array) ObjectAccess::getPropertyPath($typoScript, 'plugin') as $prefix => $pluginSettings) {
                if (!empty($pluginSettings['package'])) {
                    $collected[substr($prefix, 3)] = $pluginSettings['package'];
                }
            }
            static::$overrides = $collected;
        }
        $packageSignature = ExtensionNamingUtility::getExtensionSignature($qualifiedExtensionName);
        if (!empty(static::$overrides[$packageSignature])) {
            return static::$overrides[$packageSignature];
        }
        return [];
    }
}
