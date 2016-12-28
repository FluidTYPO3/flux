<?php
namespace FluidTYPO3\Flux\Helper;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
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
    /**
     * @param string $providerExtensionName
     * @param string $templateFilename
     * @param array $variables
     * @param string $section
     * @param array|null $paths
     * @return ProviderInterface
     */
    public function configureContentTypeFromTemplateFile(
        $providerExtensionName,
        $templateFilename,
        $variables = [],
        $section = 'Configuration',
        $paths = null
    ) {
        $controllerName = 'Content';
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $emulatedControllerAction = lcfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
        $emulatedPluginName = ucfirst($emulatedControllerAction);
        $controllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($providerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);

        $this->validateContentController($controllerClassName, $fullContentType);
        $this->configureContentTypeForController($providerExtensionName, $controllerClassName, $emulatedControllerAction);

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
        $provider->setTemplatePaths($paths);
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
     * @throws \RuntimeException
     * @return void
     */
    protected function validateContentController($controllerClassName, $contentType)
    {
        // Sanity check:
        if (!class_exists($controllerClassName)) {
            throw new \RuntimeException(
                sprintf(
                    'Class "%s" not found; Flux cannot render desired custom content type "%s"!',
                    $controllerClassName,
                    $contentType
                )
            );
        }
        if (!is_a($controllerClassName, AbstractFluxController::class, true)) {
            throw new \RuntimeException(
                sprintf(
                    'Class "%s" exists but is not a subclass of "%s", please switch parent class!',
                    $controllerClassName,
                    AbstractFluxController::class
                )
            );
        }
    }

    /**
     * @param string $providerExtensionName
     * @param string $contentType
     * @param ProviderInterface $provider
     * @param string $pluginName
     * @return void
     */
    public function registerContentType(
        $providerExtensionName,
        $contentType,
        ProviderInterface $provider,
        $pluginName
    ) {
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

        $this->initializeIfRequired();
        $this->registerExtbasePluginForForm($providerExtensionName, $pluginName, $form);
        $this->addPageTsConfig($providerExtensionName, $form, $contentType);
        $this->addBoilerplateTableConfiguration($contentType);
    }

    /**
     * Create the TCA necessary to drive this custom CType
     *
     * @param string $contentType
     * @return void
     */
    protected function addBoilerplateTableConfiguration($contentType)
    {
        $showItem = '
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.general;general,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.header;header,pi_flexform,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.appearance,layout;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:layout_formlabel,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.appearanceLinks;appearanceLinks,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,hidden;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:field.default.hidden,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:palette.access;access,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.extended,rowDescription,
                --div--;LLL:EXT:lang/locallang_tca.xlf:sys_category.tabs.category,categories, 
                --div--;LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.tabs.relation, tx_flux_parent, tx_flux_column, tx_flux_children
            ';
        $GLOBALS['TCA']['tt_content']['types'][$contentType]['showitem'] = $showItem;
        $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['ds']['*,' . $contentType] = [];
        ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'pi_flexform', $contentType);
    }

    /**
     * @param string $providerExtensionName
     * @param Form $form
     * @param string $contentType
     * @return void
     */
    protected function addPageTsConfig($providerExtensionName, Form $form, $contentType)
    {
        $formId = $form->getId();
        $icon = $form->getOption(Form::OPTION_ICON);

        // Icons required solely for use in the "new content element" wizard
        $extensionKey = ExtensionNamingUtility::getExtensionKey($providerExtensionName);
        $defaultIcon = ExtensionManagementUtility::extPath($extensionKey, 'ext_icon.gif');
        $iconIdentifier = $extensionKey . '-' . $formId;
        $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
        $iconRegistry->registerIcon($iconIdentifier, BitmapIconProvider::class, ['source' => $icon ?: $defaultIcon]);

        // Registration for "new content element" wizard to show our new CType (otherwise, only selectable via "Content type" drop-down)
        ExtensionManagementUtility::addPageTSConfig(
            sprintf(
                'mod.wizards.newContentElement.wizardItems.fluxContent.elements.%s {
                    iconIdentifier = %s
                    title = LLL:EXT:%s/Resources/Private/Language/locallang.xlf:flux.%s
                    description = LLL:EXT:%s/Resources/Private/Language/locallang.xlf:flux.%s.description
                    tt_content_defValues {
                        CType = %s
                    }
                }',
                $formId,
                $iconIdentifier,
                $extensionKey,
                $formId,
                $extensionKey,
                $formId,
                $contentType
            )
        );
    }

    /**
     * @param string $providerExtensionName
     * @param string $pluginName
     * @param Form $form
     * @return void
     */
    protected function registerExtbasePluginForForm($providerExtensionName, $pluginName, Form $form)
    {
        $formId = $form->getId();
        $icon = $form->getOption(Form::OPTION_ICON);

        ExtensionUtility::registerPlugin(
            $providerExtensionName,
            $pluginName,
            sprintf(
                'LLL:EXT:%s/Resources/Private/Language/locallang.xlf:flux.%s',
                ExtensionNamingUtility::getExtensionKey($providerExtensionName),
                $form->getId()
            ),
            $icon
        );
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
            ExtensionManagementUtility::addPageTSConfig(
                'mod.wizards.newContentElement.wizardItems.fluxContent {
                    header = LLL:EXT:flux/Resources/Private/Language/locallang.xlf:newContentWizard.fluxContent
                    show = *
                    elements {
                    }'
            );
            $initialized = true;
        }
    }
}
