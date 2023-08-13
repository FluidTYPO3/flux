<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Quick-access API methods to easily integrate with Flux
 */
class Core
{
    const CONTROLLER_ALL = '_all';

    protected static array $providers = [];
    private static array $unregisteredProviders = [];
    protected static array $extensions = [
        self::CONTROLLER_ALL => []
    ];
    protected static array $queuedContentTypeRegistrations = [];

    public static function getQueuedContentTypeRegistrations(): array
    {
        return self::$queuedContentTypeRegistrations;
    }

    public static function clearQueuedContentTypeRegistrations(): void
    {
        self::$queuedContentTypeRegistrations = [];
    }

    public static function registerProviderExtensionKey(
        string $extensionKey,
        string $providesControllerName = self::CONTROLLER_ALL
    ): void {
        if ($providesControllerName === 'Content' && !ExtensionManagementUtility::isLoaded('fluidcontent')) {
            // Special temporary case - when fluidcontent is not installed, Flux takes over and registers all
            // detected template files as native CTypes. Remove if/when fluidcontent is discontinued.
            $legacyKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
            $templateRootPath = ExtensionManagementUtility::extPath($legacyKey, 'Resources/Private/Templates/Content/');
            /** @var ContentTypeManager $contentTypeManager */
            $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
            $finder = Finder::create()->in($templateRootPath)->name('*.html')->sortByName();
            foreach ($finder->files() as $file) {
                /** @var \SplFileInfo $file */
                $contentTypeName = str_replace('_', '', $legacyKey)
                    . '_'
                    . strtolower(substr(str_replace(DIRECTORY_SEPARATOR, '', $file->getRelativePathname()), 0, -5));
                self::registerTemplateAsContentType($extensionKey, $file->getPathname(), $contentTypeName);
                $contentTypeManager->registerTypeName($contentTypeName);
            }
        }
        if (false === isset(self::$extensions[$providesControllerName])) {
            self::$extensions[$providesControllerName] = [];
        }

        if (false === in_array($extensionKey, self::$extensions[$providesControllerName])) {
            $overrides = HookHandler::trigger(
                HookHandler::PROVIDER_EXTENSION_REGISTERED,
                [
                    'extensionKey' => $extensionKey,
                    'providesControllerName' => $providesControllerName
                ]
            );
            array_push(self::$extensions[$overrides['providesControllerName']], $overrides['extensionKey']);
        }
    }

    public static function getRegisteredProviderExtensionKeys(string $forControllerName): array
    {
        if (true === isset(self::$extensions[$forControllerName])) {
            return array_unique(
                array_merge(self::$extensions[self::CONTROLLER_ALL], self::$extensions[$forControllerName])
            );
        }
        return self::$extensions[self::CONTROLLER_ALL];
    }

    /**
     * Registers a class implementing one of the Flux ConfigurationProvider
     * interfaces.
     *
     * @param class-string|object $classNameOrInstance
     */
    public static function registerConfigurationProvider($classNameOrInstance): void
    {
        $alreadyRegistered = in_array($classNameOrInstance, self::$providers);
        $alreadyUnregistered = in_array($classNameOrInstance, self::$unregisteredProviders);
        if (!$alreadyUnregistered && !$alreadyRegistered) {
            $classNameOrInstance = HookHandler::trigger(
                HookHandler::PROVIDER_REGISTERED,
                [
                    'provider' => $classNameOrInstance
                ]
            )['provider'];
            array_push(self::$providers, $classNameOrInstance);
        }
    }

    /**
     * Registers a Fluid template for use as a Dynamic Flex Form template in the
     * style of Flux's Fluid Content Element and Fluid Page configurations. See
     * documentation web site for more detailed information about how to
     * configure such a FlexForm template.
     *
     * Note: you can point to your Model Object templates and place the
     * configuration in these templates - and get automatically transformed
     * values from your FlexForms, i.e. a Domain Object instance from a "group"
     * type select box or an ObjectStorage from a list of records. Usual output
     * is completely ignored, only the "Configuration" section is considered.
     *
     * @param string $extensionKey The extension key which registered this FlexForm
     * @param string $pluginSignature The plugin signature this FlexForm belongs to
     * @param string $templateFilename Location of the Fluid template containing field definitions
     * @param array $variables Optional array of variables to pass to Fluid template
     * @param string|null $section Optional section name containing the configuration
     * @param array|null $paths Optional paths array / Closure to return paths
     * @param string $fieldName Optional fieldname if not from pi_flexform
     */
    public static function registerFluidFlexFormPlugin(
        string $extensionKey,
        string $pluginSignature,
        string $templateFilename,
        array $variables = [],
        ?string $section = null,
        ?array $paths = null,
        string $fieldName = 'pi_flexform'
    ): ProviderInterface {
        $splitSignature = explode('_', $pluginSignature, 2);
        $pluginName = GeneralUtility::underscoredToUpperCamelCase(end($splitSignature));

        /** @var ProviderInterface $provider */
        $provider = GeneralUtility::makeInstance(Provider::class);
        $provider->setTableName('tt_content');
        $provider->setFieldName($fieldName);
        $provider->setExtensionKey($extensionKey);
        $provider->setListType($pluginSignature);
        $provider->setPluginName($pluginName);
        $provider->setTemplatePathAndFilename($templateFilename);
        $provider->setTemplateVariables($variables);
        $provider->setTemplatePaths($paths);
        $provider->setConfigurationSectionName($section);
        self::registerConfigurationProvider($provider);
        return $provider;
    }

    /**
     * Same as registerFluidFlexFormPlugin, but uses a content object type for
     * resolution - use this if you registered your Extbase plugin as a content
     * object in your localconf.
     *
     * @param string $extensionKey The extension key which registered this FlexForm
     * @param string $contentObjectType The cType of the object you registered
     * @param string $templateFilename Location of the Fluid template containing field definitions
     * @param array $variables Optional array of variables to pass to Fluid template
     * @param string|null $section Optional section name containing the configuration
     * @param array|null $paths Optional paths array / Closure to return paths
     * @param string $fieldName Optional fieldname if not from pi_flexform
     */
    public static function registerFluidFlexFormContentObject(
        string $extensionKey,
        string $contentObjectType,
        string $templateFilename,
        array $variables = [],
        ?string $section = null,
        ?array $paths = null,
        string $fieldName = 'pi_flexform'
    ): ProviderInterface {
        /** @var ProviderInterface $provider */
        $provider = GeneralUtility::makeInstance(Provider::class);
        $provider->setTableName('tt_content');
        $provider->setFieldName($fieldName);
        $provider->setExtensionKey($extensionKey);
        $provider->setTemplatePathAndFilename($templateFilename);
        $provider->setTemplateVariables($variables);
        $provider->setTemplatePaths($paths);
        $provider->setConfigurationSectionName($section);
        $provider->setContentObjectType($contentObjectType);
        self::registerConfigurationProvider($provider);
        return $provider;
    }

    /**
     * Same as registerFluidFlexFormPlugin, but enables registering FlexForms
     * for any TCA field (type "flex") or field whose TCA you have overridden
     * to display as a FlexForm.
     *
     * @param string $table The SQL table this FlexForm is bound to
     * @param string|null $fieldName The SQL field this FlexForm is bound to. If empty, binds to any/every field
     * @param string $templateFilename Location of the Fluid template containing field definitions
     * @param array $variables Optional array of variables to pass to Fluid template
     * @param string|null $section Optional section name containing the configuration
     * @param array|null $paths Optional paths array / Closure to return paths
     */
    public static function registerFluidFlexFormTable(
        string $table,
        ?string $fieldName,
        string $templateFilename,
        array $variables = [],
        ?string $section = null,
        ?array $paths = null
    ): ProviderInterface {
        /** @var ProviderInterface $provider */
        $provider = GeneralUtility::makeInstance(Provider::class);
        $provider->setTableName($table);
        $provider->setFieldName($fieldName);
        $provider->setTemplatePathAndFilename($templateFilename);
        $provider->setTemplateVariables($variables);
        $provider->setTemplatePaths($paths);
        $provider->setConfigurationSectionName($section);
        self::registerConfigurationProvider($provider);
        return $provider;
    }

    /**
     * Register a template directly for use as a custom CType. Once registered
     * the CType will appear in the "Flux content" tab in the new content
     * wizard, and will be driven by either a custom controller if one is
     * specified or detected by convention; or render through the vanilla
     * ContentController provided with Flux.
     *
     * @param string $providerExtensionName Vendor.ExtensionName format of extension scope of the template file
     * @param string $templateFilename Absolute path to template file containing Flux definition, EXT:... allowed
     * @param string|null $contentTypeName Optional override for the CType value this template will use
     * @param string|null $providerClassName Optional custom class implementing ProviderInterface from Flux
     */
    public static function registerTemplateAsContentType(
        string $providerExtensionName,
        string $templateFilename,
        ?string $contentTypeName = null,
        ?string $providerClassName = Provider::class
    ): void {
        if (!PathUtility::isAbsolutePath($templateFilename)) {
            $templateFilename = static::getAbsolutePathForFilename($templateFilename);
        }

        self::$queuedContentTypeRegistrations[] = [
            $providerExtensionName,
            $templateFilename,
            $providerClassName,
            $contentTypeName,
            null,
            null
        ];
    }

    public static function unregisterConfigurationProvider(string $providerClassName): void
    {
        if (true === in_array($providerClassName, self::$providers)) {
            $index = array_search($providerClassName, self::$providers);
            unset(self::$providers[$index]);
        } elseif (false === in_array($providerClassName, self::$unregisteredProviders)) {
            array_push(self::$unregisteredProviders, $providerClassName);
        }
    }

    /**
     * Gets the defined FlexForms configuration providers based on parameters
     * @return ProviderInterface[]
     */
    public static function getRegisteredFlexFormProviders(): array
    {
        reset(self::$providers);
        return self::$providers;
    }

    protected static function getAbsolutePathForFilename(string $filename): string
    {
        if (strpos($filename, '://') !== false) {
            return $filename;
        }
        return realpath($filename) ?: GeneralUtility::getFileAbsFileName($filename);
    }
}
