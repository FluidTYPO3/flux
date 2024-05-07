<?php
namespace FluidTYPO3\Flux\Builder;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Controller\AbstractFluxController;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\Interfaces\BasicProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ContentTypeProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FluidProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\RecordProviderInterface;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @param class-string $providerClassName
     * @param string|null $contentType
     * @param string $defaultControllerExtensionName
     * @param string|null $controllerActionName
     * @return ProviderInterface
     */
    public function configureContentTypeFromTemplateFile(
        string $providerExtensionName,
        string $templateFilename,
        string $providerClassName = Provider::class,
        ?string $contentType = null,
        string $defaultControllerExtensionName = 'FluidTYPO3.Flux',
        ?string $controllerActionName = null
    ): ProviderInterface {
        /** @var BasicProviderInterface $provider */
        $provider = GeneralUtility::makeInstance($providerClassName);
        if (!$provider instanceof BasicProviderInterface
            || !$provider instanceof RecordProviderInterface
            || !$provider instanceof ControllerProviderInterface
            || !$provider instanceof FluidProviderInterface
            || !$provider instanceof ContentTypeProviderInterface
        ) {
            throw new \RuntimeException(
                sprintf(
                    'The Flux Provider class "%s" must implement at least the following interfaces to work as '
                    . 'content type Provider: %s',
                    $providerClassName,
                    implode(
                        ',',
                        [
                            BasicProviderInterface::class,
                            RecordProviderInterface::class,
                            ControllerProviderInterface::class,
                            FluidProviderInterface::class,
                            ContentTypeProviderInterface::class
                        ]
                    )
                ),
                1690816678
            );
        }

        $section = 'Configuration';
        $controllerName = 'Content';
        $pluginName = null;
        if ($controllerActionName === null) {
            // Determine which plugin name and controller action to emulate with this CType, base on file name.
            $controllerActionName = lcfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
            if ($contentType) {
                $pluginNamePart = $this->getPluginNamePartFromContentType($contentType) ?? $contentType;
                if ($pluginNamePart !== null && strtolower($pluginNamePart) !== strtolower($controllerActionName)) {
                    $controllerActionName = lcfirst($pluginNamePart);
                }
            }
            $pluginName = ucfirst(strtolower($controllerActionName));
        }

        $controllerClassName = str_replace(
            '.',
            '\\',
            $defaultControllerExtensionName
        ) . '\\Controller\\' . $controllerName . 'Controller';
        $localControllerClassName = str_replace(
            '.',
            '\\',
            $providerExtensionName
        ) . '\\Controller\\' . $controllerName . 'Controller';
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($providerExtensionName));
        $fullContentType = $contentType ?: $extensionSignature . '_' . strtolower((string) $pluginName);
        if (!$this->validateContentController($localControllerClassName)) {
            class_alias($controllerClassName, $localControllerClassName);
        }
        $this->configureContentTypeForController(
            $providerExtensionName,
            $controllerClassName,
            $controllerActionName,
            $contentType
        );

        $provider->setFieldName('pi_flexform');
        $provider->setTableName('tt_content');
        $provider->setExtensionKey($providerExtensionName);
        $provider->setControllerName($controllerName);
        $provider->setControllerAction($controllerActionName);
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

    protected function configureContentTypeForController(
        string $providerExtensionName,
        string $controllerClassName,
        string $controllerAction,
        ?string $contentType
    ): void {
        $emulatedPluginName = ucfirst(strtolower($controllerAction));
        $controllerName = $this->getControllerNameForPluginRegistration($controllerClassName);
        $extensionIdentity = $this->getExtensionIdentityForPluginRegistration($providerExtensionName);

        // Sanity check: if controller does not implement a custom action method matching template, default to "render"
        if (!method_exists($controllerClassName, $controllerAction . 'Action')) {
            $controllerAction = 'render';
        }

        // Configure an actual Extbase plugin. This is required in order to render our new CType - but the controller
        // and View can be inherited from Flux/Fluidcontent as to reduce the amount of boilerplate that will be
        // required, and to allow using Flux forms in the template file.
        ExtensionUtility::configurePlugin(
            $extensionIdentity,
            $emulatedPluginName,
            [$controllerName => $controllerAction . ',error'],
            [],
            ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
        );

        if ($contentType !== null && $this->getPluginNamePartFromContentType($contentType) === null) {
            // Content type is registered as a root content type (not scoped within a specific extension). We need to
            // relocate the TypoScript that was registered for the pseudo-plugin, to make sure it gets assigned in the
            // right place (e.g. tt_content.text, instead of tt_content.myext_text). We do this by copying the
            // TypoScript that ExtensionUtility::configurePlugin adds with a plugin name prefix, into a non-prefixed
            // location - and finally clearing the TypoScript object that ExtensionUtility::configurePlugin created.
            // This allows a content type registered with \FluidTYPO3\Flux\Core::registerTemplateAsContentType in an
            // ext_localconf.php file to correctly override TYPO3's core content types if a core content type name was
            // used in the registration.
            $targetContentTypeName = GeneralUtility::camelCaseToLowerCaseUnderscored($contentType);
            $sourceContentTypeName = strtolower(
                strtolower($extensionIdentity) .
                '_' .
                str_replace('_', '', $contentType)
            );
            ExtensionManagementUtility::addTypoScript(
                $emulatedPluginName . '_' . $controllerName . '_' . $controllerAction,
                'setup',
                'tt_content.' . $targetContentTypeName . ' < tt_content.' . $sourceContentTypeName .
                PHP_EOL .
                'tt_content.' . $sourceContentTypeName . ' >',
                'defaultContentRendering'
            );
        }
    }

    protected function validateContentController(string $controllerClassName): bool
    {
        return is_a($controllerClassName, AbstractFluxController::class, true);
    }

    public function registerContentType(
        string $providerExtensionName,
        string $contentType,
        ProviderInterface $provider
    ): void {
        // Provider *must* be able to return a Form without any global configuration or specific content
        // record being passed to it. We test this now to fail early if any errors happen during Form fetching.
        $form = $provider->getForm(['CType' => $contentType]);
        if (!$form) {
            return;
        }
        try {
            $form->setExtensionName($providerExtensionName);
        } catch (\Exception $error) {
            // Possible serialization error!
            // Unfortunately we must do pokemon-style exception catching since serialization
            // errors use the most base Exception class in PHP. So instead we check for a
            // specific dispatcher in the stack trace and re-throw if not matched.
            $pitcher = $error->getTrace()[0] ?? false;
            if ($pitcher && ($pitcher['class'] ?? '') !== 'SplObjectStorage'
                && $pitcher['function'] !== 'serialize'
            ) {
                throw $error;
            }
        }

        $icon = $this->addIcon($form, $contentType);
        $this->addPageTsConfig($form, $contentType, $icon);

        ExtensionManagementUtility::addPlugin(
            [
                $form->getLabel(),
                $contentType,
                $icon,
            ],
            'CType',
            $providerExtensionName
        );

        /** @var \Countable $fields */
        $fields = $form->getFields();
        if (count($fields) > 0) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
                'tt_content',
                'pi_flexform',
                $provider->getContentObjectType(),
                'after:header'
            );
        }
    }

    protected function addIcon(Form $form, string $contentType): string
    {
        if (isset($GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType])) {
            return $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType];
        }
        $icon = MiscellaneousUtility::getIconForTemplate($form);
        if (!$icon) {
            $icon = ExtensionManagementUtility::extPath('flux', 'Resources/Public/Icons/Extension.svg');
        }
        $iconIdentifier = $this->createIcon($icon, $contentType);
        $GLOBALS['TCA']['tt_content']['ctrl']['typeicon_classes'][$contentType] = $iconIdentifier;
        return $iconIdentifier;
    }

    /**
     * Create the TCA necessary to drive this custom CType
     */
    public function addBoilerplateTableConfiguration(string $contentType): void
    {
        if (!isset($GLOBALS['TCA']['tt_content']['types'][$contentType]['showitem'])) {
            $showItem = CompatibilityRegistry::get(static::DEFAULT_SHOWITEM);
            $GLOBALS['TCA']['tt_content']['types'][$contentType]['showitem'] = $showItem;
        }
        ExtensionManagementUtility::addToAllTCAtypes('tt_content', 'pi_flexform', $contentType);
    }

    protected function addPageTsConfig(Form $form, string $contentType, string $icon): void
    {
        // Icons required solely for use in the "new content element" wizard
        $formId = $form->getId() ?: $contentType;
        /** @var string|null $group */
        $group = $form->getOption(FormOption::GROUP);
        $groupName = $this->sanitizeString($group ?? 'fluxContent');
        $extensionName = $form->getExtensionName() ?? 'FluidTYPO3.Flux';
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);

        $labelSubReference = 'flux.newContentWizard.' . $groupName;
        $labelExtensionKey = $groupName === 'fluxContent' ? 'flux' : $extensionKey;
        $labelReference = 'LLL:EXT:'
            . $labelExtensionKey
            . $form->getLocalLanguageFileRelativePath()
            . ':'
            . $labelSubReference;
        $this->initializeNewContentWizardGroup(
            $groupName,
            $labelReference
        );

        // Registration for "new content element" wizard to show our new CType
        // (otherwise, only selectable via "Content type" drop-down)
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
                $icon,
                $form->getLabel(),
                $form->getDescription(),
                $contentType,
                $groupName,
                $formId
            )
        );
    }

    protected function sanitizeString(string $string): string
    {
        $pattern = '/([^a-z0-9\-]){1,}/i';
        $replaced = (string) preg_replace($pattern, '_', $string);
        $replaced = trim($replaced, '_');
        return empty($replaced) ? md5($string) : $replaced;
    }

    protected function initializeNewContentWizardGroup(string $groupName, string $groupLabel): void
    {
        static $groups = [];
        if (isset($groups[$groupName])) {
            return;
        }

        if (in_array($groupName, ['common', 'menu', 'special', 'forms', 'plugins'], true)) {
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
                'header = ' . $groupLabel
            )
        );
        $groups[$groupName] = true;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function createIcon(string $icon, string $contentType): string
    {
        return MiscellaneousUtility::createIcon(
            $icon,
            'content-' . $contentType
        );
    }

    private function getExtensionIdentityForPluginRegistration(string $extensionIdentity): string
    {
        if (($dotPosition = strpos($extensionIdentity, '.'))) {
            $extensionIdentity = substr($extensionIdentity, $dotPosition + 1);
        }
        return $extensionIdentity;
    }

    private function getControllerNameForPluginRegistration(string $controllerClassName): string
    {
        return $controllerClassName;
    }

    private function getPluginNamePartFromContentType(string $contentType): ?string
    {
        $underscorePosition = strpos($contentType, '_');
        if ($underscorePosition === false) {
            return null;
        }
        return GeneralUtility::underscoredToUpperCamelCase(substr($contentType, strpos($contentType, '_') + 1));
    }
}
