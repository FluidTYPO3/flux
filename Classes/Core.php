<?php
namespace FluidTYPO3\Flux;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * Quick-access API methods to easily integrate with Flux
 */
class Core
{

    const CONTROLLER_ALL = '_all';

    /**
     * Contains all ConfigurationProviders registered with Flux
     * @var array
     */
    private static $providers = [];

    /**
     * @var array
     */
    protected static $pipes = [];

    /**
     * @var array
     */
    protected static $outlets = [];

    /**
     * Contains all Forms for tables registered with Flux
     * @var array
     */
    private static $forms = [
        'models' => [],
        'tables' => [],
        'packages' => []
    ];

    /**
     * Contains ConfigurationProviders which have been unregistered
     * @var array
     */
    private static $unregisteredProviders = [];

    /**
     * Contains all extensions registered with Flux
     * @var array
     */
    private static $extensions = [
        self::CONTROLLER_ALL => []
    ];

    /**
     * Contains all programatically added TypoScript configuration files for auto-inclusion
     * @var array
     * @deprecated To be removed in next major release
     */
    private static $staticTypoScript = [];

    /**
     * Contains queued instructions to call \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin in later hook
     * @var array
     */
    private static $queuedContentTypeRegistrations = [];

    /**
     * @return array
     */
    public static function getStaticTypoScript()
    {
        return self::$staticTypoScript;
    }

    /**
     * @return array
     */
    public static function getQueuedContentTypeRegistrations()
    {
        return static::$queuedContentTypeRegistrations;
    }

    /**
     * @param mixed $locationOrLocations
     * @return void
     */
    public static function addStaticTypoScript($locationOrLocations)
    {
        GeneralUtility::logDeprecatedFunction();
        if (true === is_array($locationOrLocations) || true === $locationOrLocations instanceof \Traversable) {
            foreach ($locationOrLocations as $location) {
                self::addStaticTypoScript($location);
            }
        } else {
            if (false === in_array($locationOrLocations, self::$staticTypoScript)) {
                array_push(self::$staticTypoScript, $locationOrLocations);
            }
        }
    }

    /**
     * @param string $table
     * @param Form $form
     * @return void
     */
    public static function registerFormForTable($table, Form $form)
    {
        if (null === $form->getName()) {
            $form->setName($table);
        }
        if (null === $form->getExtensionName() && true === isset($GLOBALS['_EXTKEY'])) {
            $form->setExtensionName(GeneralUtility::underscoredToUpperCamelCase($GLOBALS['_EXTKEY']));
        }
        self::$forms['tables'][$table] = $form;
    }

    /**
     * Registers automatic Form instance building and use as TCA for a model object class/table.
     *
     * @param string $className
     * @return void
     */
    public static function registerAutoFormForModelObjectClassName($className)
    {
        GeneralUtility::logDeprecatedFunction();
        self::registerFormForModelObjectClassName($className);
    }

    /**
     * Registers a Form instance to use when TCA for a model object class/table is requested.
     *
     * @param string $className
     * @param Form $form
     * @return void
     */
    public static function registerFormForModelObjectClassName($className, Form $form = null)
    {
        GeneralUtility::logDeprecatedFunction();
        if (null !== $form && true === isset($GLOBALS['_EXTKEY']) && null === $form->getExtensionName()) {
            $form->setExtensionName(GeneralUtility::underscoredToUpperCamelCase($GLOBALS['_EXTKEY']));
        }
        self::$forms['models'][$className] = $form;
    }

    /**
     * @param string $className
     * @return void
     */
    public static function unregisterFormForModelObjectClassName($className)
    {
        GeneralUtility::logDeprecatedFunction();
        if (true === isset(self::$forms['models'][$className])) {
            unset(self::$forms['models'][$className]);
        }
    }

    /**
     * Registers a package key (Vendor.ExtensionName) which is expected to
     * contain Domain/Form/{$modelName}Form classes.
     *
     * @param string $packageName
     * @return void
     */
    public static function registerFluxDomainFormPackage($packageName)
    {
        GeneralUtility::logDeprecatedFunction();
        self::$forms['packages'][$packageName] = true;
    }

    /**
     * @param string $packageName
     * @return void
     */
    public static function unregisterFluxDomainFormPackage($packageName)
    {
        GeneralUtility::logDeprecatedFunction();
        if (true === isset(self::$forms['packages'][$packageName])) {
            unset(self::$forms['packages'][$packageName]);
        }
    }

    /**
     * @param string $extensionKey
     * @param string $providesControllerName
     * @return void
     */
    public static function registerProviderExtensionKey($extensionKey, $providesControllerName = self::CONTROLLER_ALL)
    {
        if ($providesControllerName === 'Content' && !ExtensionManagementUtility::isLoaded('fluidcontent')) {
            // Special temporary case - when fluidcontent is not installed, Flux takes over and registers all
            // detected template files as native CTypes. Remove if/when fluidcontent is discontinued.
            $legacyKey = ExtensionNamingUtility::getExtensionKey($extensionKey);
            $templateRootPath = ExtensionManagementUtility::extPath($legacyKey, 'Resources/Private/Templates/Content/');
            foreach (GeneralUtility::getFilesInDir($templateRootPath, 'html') as $file) {
                static::registerTemplateAsContentType($extensionKey, $templateRootPath . $file);
            }
            return;
        }
        if (false === isset(self::$extensions[$providesControllerName])) {
            self::$extensions[$providesControllerName] = [];
        }

        if (false === in_array($extensionKey, self::$extensions[$providesControllerName])) {
            array_push(self::$extensions[$providesControllerName], $extensionKey);
        }
    }

    /**
     * @param string $forControllerName
     * @return array
     */
    public static function getRegisteredProviderExtensionKeys($forControllerName)
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
     * @param string|object $classNameOrInstance
     * @return void
     * @throws \RuntimeException
     */
    public static function registerConfigurationProvider($classNameOrInstance)
    {
        $alreadyRegistered = in_array($classNameOrInstance, self::$providers);
        $alreadyUnregistered = in_array($classNameOrInstance, self::$unregisteredProviders);
        if (!$alreadyUnregistered && !$alreadyRegistered) {
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
     * @param mixed $extensionKey The extension key which registered this FlexForm
     * @param mixed $pluginSignature The plugin signature this FlexForm belongs to
     * @param mixed $templateFilename Location of the Fluid template containing field definitions
     * @param mixed $variables Optional array of variables to pass to Fluid template
     * @param mixed|NULL Optional section name containing the configuration
     * @param mixed|NULL Optional paths array / Closure to return paths
     * @param string $fieldName Optional fieldname if not from pi_flexform
     * @return ProviderInterface
     */
    public static function registerFluidFlexFormPlugin(
        $extensionKey,
        $pluginSignature,
        $templateFilename,
        $variables = [],
        $section = null,
        $paths = null,
        $fieldName = 'pi_flexform'
    ) {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var $provider ProviderInterface */
        $provider = $objectManager->get(Provider::class);
        $provider->setTableName('tt_content');
        $provider->setFieldName($fieldName);
        $provider->setExtensionKey($extensionKey);
        $provider->setListType($pluginSignature);
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
     * @param mixed $extensionKey The extension key which registered this FlexForm
     * @param mixed $contentObjectType The cType of the object you registered
     * @param mixed $templateFilename Location of the Fluid template containing field definitions
     * @param mixed $variables Optional array of variables to pass to Fluid template
     * @param mixed|NULL Optional section name containing the configuration
     * @param mixed|NULL Optional paths array / Closure to return paths
     * @param string $fieldName Optional fieldname if not from pi_flexform
     * @return ProviderInterface
     */
    public static function registerFluidFlexFormContentObject(
        $extensionKey,
        $contentObjectType,
        $templateFilename,
        $variables = [],
        $section = null,
        $paths = null,
        $fieldName = 'pi_flexform'
    ) {
        /** @var $objectManager ObjectManagerInterface */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var $provider ProviderInterface */
        $provider = $objectManager->get(Provider::class);
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
     * @param mixed $table The SQL table this FlexForm is bound to
     * @param mixed $fieldName The SQL field this FlexForm is bound to
     * @param mixed $templateFilename Location of the Fluid template containing field definitions
     * @param mixed $variables Optional array of variables to pass to Fluid template
     * @param mixed|NULL Optional section name containing the configuration
     * @param mixed|NULL Optional paths array / Closure to return paths
     * @return ProviderInterface
     */
    public static function registerFluidFlexFormTable(
        $table,
        $fieldName,
        $templateFilename,
        $variables = [],
        $section = null,
        $paths = null
    ) {
        /** @var $objectManager ObjectManagerInterface */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var $provider ProviderInterface */
        $provider = $objectManager->get(Provider::class);
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
     */
    public static function registerTemplateAsContentType($providerExtensionName, $templateFilename)
    {
        if (strpos($templateFilename, '/') !== 0) {
            $templateFilename = GeneralUtility::getFileAbsFileName($templateFilename);
        }

        static::$queuedContentTypeRegistrations[] = [
            $providerExtensionName,
            $templateFilename,
        ];
    }

    /**
     * @param string $providerClassName
     * @return void
     */
    public static function unregisterConfigurationProvider($providerClassName)
    {
        if (true === in_array($providerClassName, self::$providers)) {
            $index = array_search($providerClassName, self::$providers);
            unset(self::$providers[$index]);
        } elseif (false === in_array($providerClassName, self::$unregisteredProviders)) {
            array_push(self::$unregisteredProviders, $providerClassName);
        }
    }

    /**
     * @param string $typeOrClassName
     * @param string $insteadOfNativeType
     * @return void
     */
    public static function registerPipe($typeOrClassName, $insteadOfNativeType = null)
    {
        $key = null === $insteadOfNativeType ? $typeOrClassName : $insteadOfNativeType;
        self::$pipes[$key] = $typeOrClassName;
    }

    /**
     * @param string $typeOrClassName
     */
    public static function unregisterPipe($typeOrClassName)
    {
        if (true === in_array($typeOrClassName, self::$pipes)) {
            $index = array_search($typeOrClassName, self::$pipes);
            unset(self::$pipes[$index]);
        }
    }

    /**
     * @param string $typeOrClassName
     * @param string $insteadOfNativeType
     * @return void
     */
    public static function registerOutlet($typeOrClassName, $insteadOfNativeType = null)
    {
        $key = null === $insteadOfNativeType ? $typeOrClassName : $insteadOfNativeType;
        self::$outlets[$key] = $typeOrClassName;
    }

    /**
     * @param string $typeOrClassName
     */
    public static function unregisterOutlet($typeOrClassName)
    {
        if (true === in_array($typeOrClassName, self::$outlets)) {
            $index = array_search($typeOrClassName, self::$outlets);
            unset(self::$outlets[$index]);
        }
    }

    /**
     * Gets the defined FlexForms configuration providers based on parameters
     * @return ProviderInterface[]
     */
    public static function getRegisteredFlexFormProviders()
    {
        reset(self::$providers);
        return self::$providers;
    }

    /**
     * @return Form[]
     */
    public static function getRegisteredFormsForTables()
    {
        return self::$forms['tables'];
    }

    /**
     * @param string $table
     * @return Form|NULL
     */
    public static function getRegisteredFormForTable($table)
    {
        if (true === isset(self::$forms['tables'][$table])) {
            return self::$forms['tables'][$table];
        }
        return null;
    }

    /**
     * @return Form[]
     */
    public static function getRegisteredFormsForModelObjectClasses()
    {
        GeneralUtility::logDeprecatedFunction();
        return self::$forms['models'];
    }

    /**
     * @return Form[]
     */
    public static function getRegisteredPackagesForAutoForms()
    {
        GeneralUtility::logDeprecatedFunction();
        return self::$forms['packages'];
    }

    /**
     * @param string $class
     * @return Form|NULL
     */
    public static function getRegisteredFormForModelObjectClass($class)
    {
        GeneralUtility::logDeprecatedFunction();
        if (true === isset(self::$forms['models'][$class])) {
            return self::$forms['models'][$class];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getPipes()
    {
        return array_values(self::$pipes);
    }

    /**
     * @return array
     */
    public static function getOutlets()
    {
        return array_values(self::$outlets);
    }
}
