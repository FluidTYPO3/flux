<?php
namespace FluidTYPO3\Flux\Helper;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Controller\ContentController;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Content Type Builder
 *
 * Class to build new CType registrations based on simple
 * input variables which are then expanded to boilerplate code.
 */
class ContentTypeBuilder
{
    const DEFAULT_SHOWITEM = 'defaultShowItem';

    /**
     * @param string $providerExtensionName
     * @param string $templateFilename
     * @return ProviderInterface
     */
    public function configureContentTypeFromTemplateFile($providerExtensionName, $templateFilename)
    {
        $variables = [];
        $section = 'Configuration';
        $controllerName = 'Content';
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $emulatedControllerAction = lcfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
        $emulatedPluginName = ucfirst($emulatedControllerAction);
        $controllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($providerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        if ($this->validateContentController($controllerClassName, $fullContentType)) {
            $controllerExtensionName = $providerExtensionName;
        } else {
            $controllerClassName = ContentController::class;
            $controllerExtensionName = 'FluidTYPO3.Flux';
            $fullContentType = 'flux_' . strtolower($emulatedPluginName);
        }
        $this->configureContentTypeForController($controllerExtensionName, $controllerClassName, $emulatedControllerAction);

        /** @var Provider $provider */
        $provider = GeneralUtility::makeInstance(ObjectManager::class)->get(Provider::class);
        $provider->setFieldName('pi_flexform');
        $provider->setTableName('tt_content');
        $provider->setExtensionKey($providerExtensionName);
        $provider->setControllerName($controllerName);
        $provider->setControllerAction($emulatedControllerAction);
        $provider->setTemplatePathAndFilename($templateFilename);
        $provider->setContentObjectType($fullContentType);
        $provider->setTemplateVariables($variables);
        $provider->setConfigurationSectionName($section);

        return $provider;
    }

    /**
     * @param string $providerExtensionName
     * @param string $controllerClassName
     * @param string $controllerAction
     * @throws \RuntimeException
     * @return void
     */
    protected function configureContentTypeForController($providerExtensionName, $controllerClassName, $controllerAction)
    {
        $controllerName = substr($controllerClassName, strrpos($controllerClassName, '\\') + 1, -10);
        $emulatedPluginName = ucfirst($controllerAction);

        // Sanity check: if controller does not implement a custom method matching the template name, default to "render"
        if (!method_exists($controllerClassName, $controllerAction . 'Action')) {
            $controllerAction = 'render';
        }

        // Configure an actual Extbase plugin. This is required in order to render our new CType - but the controller and View
        // can be inherited from Flux/Fluidcontent as to reduce the amount of boilerplate that will be required, and to allow
        // using Flux forms in the template file.
        ExtensionUtility::configurePlugin(
            $providerExtensionName,
            $emulatedPluginName,
            [$controllerName => $controllerAction . ',outlet,error'],
            [],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );
    }

    /**
     * @param string $controllerClassName
     * @param string $contentType
     * @return boolean
     */
    protected function validateContentController($controllerClassName, $contentType)
    {
        // Sanity check:
        if (!class_exists($controllerClassName)) {
            GeneralUtility::devLog(
                sprintf(
                    'Class "%s" not found as controller for CType "%s"; Flux will use the default which is "%s"',
                    $controllerClassName,
                    $contentType,
                    ContentController::class
                ),
                '',
                GeneralUtility::SYSLOG_SEVERITY_INFO
            );
            return false;
        }
        if (!is_a($controllerClassName, AbstractFluxController::class, true)) {
            GeneralUtility::devLog(
                sprintf(
                    'Class "%s" exists but is not a subclass of "%s", please switch parent class!',
                    $controllerClassName,
                    AbstractFluxController::class
                ),
                '',
                GeneralUtility::SYSLOG_SEVERITY_WARNING
            );
            return false;
        }
        return true;
    }

    /**
     * @param string $providerExtensionName
     * @param string $contentType
     * @param ProviderInterface $provider
     * @param string $pluginName
     * @return void
     * @throws \Exception
     */
    public function registerContentType(
        $providerExtensionName,
        $contentType,
        ProviderInterface $provider,
        $pluginName
    ) {
        $cacheId = 'CType_' . $contentType . '_' . md5($providerExtensionName . '_' . $pluginName);
        $cache = $this->getCache();
        $form = $cache->get($cacheId);
        if (!$form) {
            // Provider *must* be able to return a Form without any global configuration or specific content
            // record being passed to it. We test this now to fail early if any errors happen during Form fetching.
            $form = $provider->getForm([]);
            if (!$form) {
                throw new \RuntimeException(
                    sprintf(
                        'Flux could not extract a Flux definition from "%s". Check that the file exists and contains ' .
                        'the necessary flux:form in the configured section "%s"',
                        $provider->getTemplatePathAndFilename([]),
                        $provider->getConfigurationSectionName([])
                    )
                );
            }
            try {
                $cache->set($cacheId, $form);
            } catch (\Exception $error) {
                // Possible serialization error!
                // Unfortunately we must do pokemon-style exception catching since serialization
                // errors use the most base Exception class in PHP. So instead we check for a
                // specific dispatcher in the stack trace and re-throw if not matched.
                $pitcher = $error->getTrace()[0] ?? false;
                if ($pitcher && $pitcher['class'] !== 'SplObjectStorage' && $pitcher['function'] !== 'serialize') {
                    throw $error;
                }
            }
        }

        $this->initializeIfRequired();
        $controllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $provider->getControllerNameFromRecord([]) . 'Controller';
        if ($this->validateContentController($controllerClassName, $contentType)) {
            $controllerExtensionName = $providerExtensionName;
        } else {
            $controllerExtensionName = 'FluidTYPO3.Flux';
        }
        $this->registerExtbasePluginForForm($controllerExtensionName, $pluginName, $form);
        $this->addPageTsConfig($form, $contentType);

        // Flush the cache entry that was generated; make sure any TypoScript overrides will take place once
        // all TypoScript is finally loaded.
        GeneralUtility::makeInstance(CacheManager::class)
            ->getCache('cache_runtime')
            ->remove('viewpaths_' . ExtensionNamingUtility::getExtensionKey($providerExtensionName));
    }

    /**
     * @param Form $form
     * @param string $contentType
     * @return string
     */
    protected function addIcon(Form $form, $contentType)
    {
        if (isset($GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType])) {
            return $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType];
        }
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        if (strpos($icon, 'EXT:') === 0 || $icon{0} !== '/') {
            $icon = GeneralUtility::getFileAbsFileName($icon);
        }
        if (!$icon) {
            $icon = ExtensionManagementUtility::extPath('flux', 'Resources/Public/Icons/Plugin.png');
        }
        $iconIdentifier = MiscellaneousUtility::createIcon(
            $icon,
            Icon::SIZE_DEFAULT,
            Icon::SIZE_DEFAULT
        );
        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType] = $iconIdentifier;
        return $iconIdentifier;
    }

    /**
     * Create the TCA necessary to drive this custom CType
     *
     * @param string $contentType
     * @return void
     */
    public function addBoilerplateTableConfiguration($contentType)
    {
        // use CompatibilityRegistry for correct DefaultData class
        $showItem = CompatibilityRegistry::get(self::DEFAULT_SHOWITEM);

        // Do not add the special IRRE nested content display (when editing parent) if workspaces is loaded.
        // When workspaces is loaded, the IRRE may contain move placeholders which cause TYPO3 to throw errors
        // if attempting to save the parent record, because new versions get created for all child records and
        // this isn't allowed for move placeholders.
        if (!ExtensionManagementUtility::isLoaded('workspaces')) {
            $showItem .= ', tx_flux_children';
        }
        $GLOBALS['TCA']['tt_content']['types'][$contentType]['showitem'] = $showItem;
        ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'pi_flexform', $contentType);
    }

    /**
     * @param Form $form
     * @param string $contentType
     * @return void
     */
    protected function addPageTsConfig(Form $form, $contentType)
    {
        // Icons required solely for use in the "new content element" wizard
        $formId = $form->getId();
        $group = $form->getOption(Form::OPTION_GROUP) ?? 'fluxContent';
        $this->initializeNewContentWizardGroup($this->sanitizeString($group), $group);

        // Registration for "new content element" wizard to show our new CType (otherwise, only selectable via "Content type" drop-down)
        ExtensionManagementUtility::addPageTSConfig(
            sprintf(
                'mod.wizards.newContentElement.wizardItems.%s.elements.%s {
                    iconIdentifier = %s
                    title = %s
                    description = %s
                    tt_content_defValues {
                        CType = %s
                    }
                }
                mod.wizards.newContentElement.wizardItems.%s.show := addToList(%s)',
                $this->sanitizeString($group),
                $formId,
                $this->addIcon($form, $contentType),
                $form->getLabel(),
                $form->getDescription(),
                $contentType,
                $group,
                $formId
            )
        );
    }

    /**
     * @param string $string
     * @return string
     */
    protected function sanitizeString($string)
    {
        $pattern = '/([^a-z0-9\-]){1,}/i';
        $replaced = preg_replace($pattern, '_', $string);
        $replaced = trim($replaced, '_');
        return empty($replaced) ? md5($string) : $replaced;
    }

    /**
     * @param string $providerExtensionName
     * @param string $pluginName
     * @param Form $form
     * @return void
     */
    protected function registerExtbasePluginForForm($providerExtensionName, $pluginName, Form $form)
    {
        ExtensionUtility::registerPlugin(
            $providerExtensionName,
            $pluginName,
            $form->getLabel(),
            MiscellaneousUtility::getIconForTemplate($form)
        );
    }

    /**
     * @param string $groupName
     * @param string $groupLabel
     */
    protected function initializeNewContentWizardGroup($groupName, $groupLabel)
    {
        static $groups = [];
        if (isset($groups[$groupName])) {
            return;
        }
        ExtensionManagementUtility::addPageTSConfig(
            sprintf(
                'mod.wizards.newContentElement.wizardItems.%s {
                    header = %s
                    show = *
                    elements {
                    }
                }',
                $groupName,
                $groupLabel
            )
        );
        $groups[$groupName] = true;
    }

    /**
     * @return void
     */
    protected function initializeIfRequired()
    {
        static $initialized = false;

        if (!$initialized) {
            // Register the stub/group/tab which will store all elements added this way. We wrap this in our Core
            // registration class to avoid this tab being added unless elements are used. Then toggle the static
            // initialized flag to avoid repeating this insertion.
            $this->initializeNewContentWizardGroup(
                'fluxContent',
                'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:newContentWizard.fluxContent'
            );
            $initialized = true;
        }
    }

    /**
     * @return FrontendInterface
     */
    protected function getCache()
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('flux');
    }
}
