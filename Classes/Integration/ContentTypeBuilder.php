<?php
namespace FluidTYPO3\Flux\Integration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Controller\ContentController;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
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
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

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
     * @param string $providerClassName
     * @param string $contentType
     * @param string $defaultControllerExtensionName
     * @return ProviderInterface
     */
    public function configureContentTypeFromTemplateFile(
        $providerExtensionName,
        $templateFilename,
        $providerClassName = Provider::class,
        $contentType = null,
        $defaultControllerExtensionName = 'FluidTYPO3.Flux'
    ) {
        $section = 'Configuration';
        $controllerName = 'Content';
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $emulatedControllerAction = lcfirst($contentType ? GeneralUtility::underscoredToUpperCamelCase(end(explode('_', $contentType, 2))) : pathinfo($templateFilename, PATHINFO_FILENAME));
        $emulatedPluginName = ucfirst($emulatedControllerAction);

        $controllerClassName = str_replace('.', '\\', $defaultControllerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        $localControllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($providerExtensionName));
        $fullContentType = $contentType ?: $extensionSignature . '_' . strtolower($emulatedPluginName);
        if (!$this->validateContentController($localControllerClassName)) {
            class_alias($controllerClassName, $localControllerClassName);
        }
        $this->configureContentTypeForController($providerExtensionName, $controllerClassName, $emulatedControllerAction);

        /** @var Provider $provider */
        $provider = GeneralUtility::makeInstance(ObjectManager::class)->get($providerClassName);
        if (
            !$provider instanceof RecordProviderInterface
            || !$provider instanceof ControllerProviderInterface
            || !$provider instanceof FluidProviderInterface
        ) {
            throw new \RuntimeException(
                sprintf(
                    'The Flux Provider class "%s" must implement at least the following interfaces to work as content type Provider: %s',
                    $providerClassName,
                    implode(',', [RecordProviderInterface::class, ControllerProviderInterface::class, FluidProviderInterface::class])
                )
            );
        }
        $provider->setFieldName('pi_flexform');
        $provider->setTableName('tt_content');
        $provider->setExtensionKey($providerExtensionName);
        $provider->setControllerName($controllerName);
        $provider->setControllerAction($emulatedControllerAction);
        $provider->setTemplatePathAndFilename($templateFilename);
        $provider->setContentObjectType($fullContentType);
        $provider->setConfigurationSectionName($section);

        return HookHandler::trigger(
            HookHandler::CONTENT_TYPE_CONFIGURED,
            [
                'provider' => $provider,
                'extensionName' => $providerExtensionName,
                'templateFilename' => $templateFilename,
                'controllerClassName' => $controllerClassName,
                'contentType' => $fullContentType
            ]
        )['provider'];
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
     * @return boolean
     */
    protected function validateContentController($controllerClassName)
    {
        return is_a($controllerClassName, AbstractFluxController::class, true);
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
            $form = $provider->getForm(['CType' => $contentType]);
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
                $form->setExtensionName($providerExtensionName);
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

        $this->registerExtbasePluginForForm($providerExtensionName, GeneralUtility::underscoredToUpperCamelCase(end(explode('_', $contentType))), $form);
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
            'content-' . $contentType
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
        $showItem = CompatibilityRegistry::get(static::DEFAULT_SHOWITEM);

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
        $formId = $form->getId() ?: $contentType;
        $group = $form->getOption(Form::OPTION_GROUP);
        $groupName = $this->sanitizeString($group ?? 'fluxContent');
        $extensionKey = ExtensionNamingUtility::getExtensionKey($form->getExtensionName());

        $labelSubReference = 'flux.newContentWizard.' . $groupName;
        $labelExtensionKey = $groupName === 'fluxContent' ? 'flux' : $extensionKey;
        $labelReference = 'LLL:EXT:' . $labelExtensionKey . $form->getLocalLanguageFileRelativePath() . ':' . $labelSubReference;
        $probedTranslation = LocalizationUtility::translate($labelReference);

        $this->initializeNewContentWizardGroup(
            $groupName,
            $probedTranslation ? $labelReference : $groupName
        );

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
                $groupName,
                $formId,
                $this->addIcon($form, $contentType),
                $form->getLabel(),
                $form->getDescription(),
                $contentType,
                $groupName,
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

        if (in_array($groupName, ['common', 'menu', 'special', 'forms', 'plugins'])) {
            return;
        }

        ExtensionManagementUtility::addPageTSConfig(
            sprintf(
                'mod.wizards.newContentElement.wizardItems.%s {
                    %s
                    elements {
                    }
                }',
                $groupName,
                $groupLabel ? 'header = ' . $groupLabel : ''
            )
        );
        $groups[$groupName] = true;
    }

    /**
     * @return FrontendInterface
     */
    protected function getCache()
    {
        return GeneralUtility::makeInstance(CacheManager::class)->getCache('flux');
    }
}
