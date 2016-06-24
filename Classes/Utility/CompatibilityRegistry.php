<?php
namespace FluidTYPO3\Flux\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Compatibility Registry
 * ----------------------
 *
 * Utility class responsible for keeping and providing
 * arbitrary values (such as configuration defaults, class
 * names, feature flags etc.) based on the version of
 * TYPO3 being used, or based on arbitrary versions of
 * packages, extensions, PHP, PHP modules, binaries etc.
 *
 * Usage example
 * -------------
 *
 * Use \FluidTYPO3\Flux\Utility\CompatibilityRegistry;
 *
 * CompatibilityRegistry::register(
 *     'FluidTYPO3\\Flux\\Backend\\Preview',
 *     [
 *         '6.2.0' => 'FluidTYPO3\\Flux\\Backend\\LegacyPreview',
 *         '7.1.0' => 'FluidTYPO3\\Flux\\Backend\\Preview'
 *     )
 * );
 *
 * $classWhichApplies = CompatibilityRegistry::get(
 *     'FluidTYPO3\\Flux\\Backend\\Preview'
 * );
 *
 * Also supports reading a specific version's applied value:
 *
 * $legacyClassName = CompatibilityRegistry::get(
 *     'FluidTYPO3\\Flux\\Backend\\Preview',
 *     '7.1.0'
 * );
 *
 * And a default to be used if nothing can be resolved:
 *
 * $classOrDefault = CompatibilityRegistry::get(
 *     'FluidTYPO3\\Flux\\Backend\\Preview',
 *     NULL,
 *     'FluidTYPO3\\Flux\\Backend\\UnsupportedPreview'
 * );
 *
 * (note that `NULL` is passed as version which means we
 * attempt to resolve the currently running version's best
 * match. It is still possible to pass a specific version
 * number here).
 *
 * Another example - say you created a backend module and
 * need it to work with a custom stylesheet on multiple
 * versions of TYPO3 which are styled differently:
 *
 * CompatibilityRegistry::register(
 *     'MyExtension',
 *     '/mySpecialNameForTheStylesheet',
 *     [
 *         '6.2.0' => 'EXT:my_extension/.../Styles.6.2.0.css',
 *         '7.2.0' => 'EXT:my_extension/.../Styles.7.2.0.css',
 *         '7.5.0' => 'EXT:my_extension/.../Styles.7.5.0.css'
 *     )
 * );
 *
 * And to retrieve:
 *
 * $styleSheet = CompatibilityRegistry::get(
 *     'MyExtension/mySpecialNameForTheStylesheet'
 * );
 *
 * And to illustrate, if...:
 *
 * - TYPO3 is 6.2.* obviously the '6.2.0' entry is used
 * - TYPO3 is 7.1.0 the '6.2.0' is used because '7.2.' is too high
 * - TYPO3 is 7.4.0 the '7.2.0' is used
 * - TYPO3 is anything at or above 7.5.0 then '7.5.0' is used
 *
 * Feature Flags
 * -------------
 *
 * The Compatibility Registry can also handle feature
 * flags for you; returning the closest matching set of
 * or individual feature flag based on version:
 *
 * CompatibilityRegistry::registerFeatureFlags(
 *     'FluidTYPO3.Flux',
 *     [
 *         '6.2.0' => ['form', 'nestedContent', 'provider', 'preview'),
 *         '7.5.0' => ['form', 'nestedContent', 'provider', 'preview', 'formengine'),
 *         '7.6.0' => ['form', 'provider', 'preview', 'formengine')
 *     )
 * );
 *
 * And to retrieve:
 *
 * if (CompatibilityRegistry::hasFeatureFlag('FluidTYPO3.Flux', 'nestedContent')) {
 *     // Would only be true until TYPO3 version hits 7.6.0, then
 *     // no longer triggers.
 * }
 *
 * Deprecation
 * -----------
 *
 * Because usage of CompatibilityRegistry is a very clear
 * intention of *supporting deprecation*, there is a built-in
 * layer warning of deprecation when you *register* either
 * values or feature flags but *do not include a currently
 * supported TYPO3 version* (as far as Flux is aware). To
 * disable this deprecation reporting simply pass TRUE as
 * the last parameter for either of the register commands:
 *
 * CompatibilityRegistry::register('MyClass', [...), TRUE);
 * CompatibilityRegistry::registerFeatureFlag('MyExt', [...), TRUE);
 *
 * Doing so merely prevents the check for whether or not you
 * include a value or feature flag for a non-deprecated TYPO3.
 *
 * Extension versions as key
 * -------------------------
 *
 * It is possible to make a registered variable or feature
 * flag not depend on the TYPO3 version but instead the
 * version of any arbitrary package - like a composer
 * dependency or version of PHP. To utilise this slightly
 * off-standard registry behavior, *manually include the
 * version you are checking against when you retrieve the
 * values from the registry*.
 *
 * Say your extension depends on extension "comments"
 * (fictional) but "comments" may be installed in several
 * versions that can be supported without breaking, as
 * long as certain features are not enabled:
 *
 * CompatibilityRegistry::registerFeatureFlags(
 *     'MyExt',
 *     [
 *         '3.1.0' => ['falRelations'),
 *         '4.0.0' => ['falRelations', 'ajax')
 *     ),
 *     TRUE
 *     // Note: *always* pass true here; the versions we record
 *     // aren't for TYPO3 so we don't want deprecation warnings.
 * );
 *
 * $extensionVersion = ExtensionManagementUtility::getExtensionVersion('comments');
 * if (CompatibilityRegistry::hasFeatureFlag('MyExt', 'ajax', $extensionVersion) {
 *     // It's okay to use our fresh feature that depends on
 *     // the version of "comments" extension, not TYPO3 itself.
 * }
 *
 * Which naturally means that only if the "comments"
 * extension is installed in at least version 4.0.0 will
 * the "ajax" feature flag query be TRUE.
 */
abstract class CompatibilityRegistry
{

    const VERSION_DEFAULT = 'default';

    /**
     * @var array
     */
    protected static $registry = [];

    /**
     * @var array
     */
    protected static $featureFlags = [];

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * @param string $scope
     * @param array $versionedVariables
     * @param boolean $deprecationWarnings
     * @return void
     */
    public static function register($scope, array $versionedVariables, $deprecationWarnings = false)
    {
        if ($deprecationWarnings
            && null === static::resolveVersionedValue($versionedVariables, self::VERSION_DEFAULT)) {
            GeneralUtility::deprecationLog(
                sprintf(
                    'FluidTYPO3.Flux: CompatibilityRegistry was given versioned variables in scope %s but the ' .
                    'versioned values do not contain at least one value that applies on this version of TYPO3 (%s)',
                    $scope,
                    static::resolveVersion(self::VERSION_DEFAULT)
                )
            );
        }
        static::$registry[$scope] = $versionedVariables;
    }

    /**
     * @param string $scope
     * @param array $versionedFeatureFlags
     * @param boolean $deprecationWarnings
     * @return void
     */
    public static function registerFeatureFlags($scope, array $versionedFeatureFlags, $deprecationWarnings = false)
    {
        if ($deprecationWarnings
            && null === static::resolveVersionedValue($versionedFeatureFlags, self::VERSION_DEFAULT)) {
            GeneralUtility::deprecationLog(
                sprintf(
                    'FluidTYPO3.Flux: CompatibilityRegistry was given versioned feature flags in scope %s but the ' .
                    'versioned values do not contain at least one set that applies on this version (%s)',
                    $scope,
                    static::resolveVersion(self::VERSION_DEFAULT)
                )
            );
        }
        static::$featureFlags[$scope] = $versionedFeatureFlags;
    }

    /**
     * @param string $scope
     * @param string|NULL $version
     * @param mixed $default
     * @return mixed
     */
    public static function get($scope, $version = self::VERSION_DEFAULT, $default = null)
    {
        $value = static::cache(static::$registry, 'registry', $scope, $version);
        if (null === $value && $default !== $value) {
            return $default;
        }
        return $value;
    }

    /**
     * @param string $scope
     * @param string $flag
     * @param string $version
     * @return boolean
     */
    public static function hasFeatureFlag($scope, $flag, $version = self::VERSION_DEFAULT)
    {
        return in_array($flag, static::getFeatureFlags($scope, $version));
    }

    /**
     * @param string $scope
     * @param string $version
     * @return array
     */
    public static function getFeatureFlags($scope, $version = self::VERSION_DEFAULT)
    {
        return (array) static::cache(static::$featureFlags, 'featureFlags', $scope, $version);
    }

    /**
     * @param string $version
     * @return string
     */
    protected static function resolveVersion($version)
    {
        return (string) (self::VERSION_DEFAULT === $version ? TYPO3_version : $version);
    }

    /**
     * @param array $versionedValues
     * @param string $version
     * @return mixed
     */
    protected static function resolveVersionedValue(array &$versionedValues, $version)
    {
        $version = static::resolveVersion($version);
        krsort($versionedValues);
        foreach ($versionedValues as $valueVersion => $value) {
            if (version_compare($version, $valueVersion, '>=')) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param array $source
     * @param string $prefix
     * @param string $scope
     * @param string $version
     * @return array|NULL
     */
    protected static function cache(array &$source, $prefix, $scope, $version)
    {
        $key = $prefix . '-' . $scope . '-' . $version;
        if (true === array_key_exists($key, static::$cache)) {
            return static::$cache[$key];
        } elseif (is_array($source[$scope])) {
            $value = static::resolveVersionedValue($source[$scope], $version);
            static::$cache[$key] = $value;
            return $value;
        } else {
            GeneralUtility::sysLog(
                sprintf(
                    'Possible misconfiguration or stale cached configuration: versioned attributes named "%s" ' .
                    'requested from %s but no such named attributes are registered. If flushing the system cache ' .
                    'does not remove this message please check your source code for possible typos in registration ' .
                    'or usages of "%s".',
                    $scope,
                    static::class,
                    $scope
                ),
                GeneralUtility::SYSLOG_SEVERITY_WARNING
            );
        }
        return null;
    }
}
