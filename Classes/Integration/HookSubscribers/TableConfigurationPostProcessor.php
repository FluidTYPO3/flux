<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ContextUtility;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Exception;

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
 */
class TableConfigurationPostProcessor implements TableConfigurationPostProcessingHookInterface
{
    /**
     * @var ContentTypeBuilder|null
     */
    static $contentTypeBuilder;

    /**
     * @param array $parameters
     * @return void
     */
    public function includeStaticTypoScriptHook(array $parameters, TemplateService $caller)
    {
        $property = new \ReflectionProperty($caller, 'extensionStaticsProcessed');
        $property->setAccessible(true);
        if (!$property->getValue($caller)) {
            $this->processData();
        }
    }

    /**
     * @return void
     */
    public function processData()
    {
        $contentTypeManager = $this->getContentTypeManager();

        foreach ($contentTypeManager->fetchContentTypes() as $contentType) {
            $contentTypeManager->registerTypeDefinition($contentType);
            Core::registerTemplateAsContentType(
                $contentType->getExtensionIdentity(),
                $contentType->getTemplatePathAndFilename(),
                $contentType->getContentTypeName(),
                $contentType->getProviderClassName()
            );
        }

        $this->spoolQueuedContentTypeRegistrations(Core::getQueuedContentTypeRegistrations());
        Core::clearQueuedContentTypeRegistrations();
    }

    /**
     * @param array $queue
     * @return void
     */
    public static function spoolQueuedContentTypeTableConfigurations(array $queue)
    {
        $contentTypeBuilder = static::getContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            list ($providerExtensionName, $templatePathAndFilename, , $contentType) = $queuedRegistration;
            $contentType = $contentType ?: static::determineContentType($providerExtensionName, $templatePathAndFilename);
            $contentTypeBuilder->addBoilerplateTableConfiguration($contentType);
        }
    }

    /**
     * @param string $providerExtensionName
     * @param string $templatePathAndFilename
     * @return string
     */
    protected static function determineContentType($providerExtensionName, $templatePathAndFilename)
    {
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $controllerExtensionName = $providerExtensionName;
        $emulatedPluginName = ucfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($controllerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        return $fullContentType;
    }

    /**
     * @param array $queue
     * @return void
     */
    protected function spoolQueuedContentTypeRegistrations(array $queue)
    {
        $contentTypeBuilder = static::getContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            /** @var ProviderInterface $provider */
            [$providerExtensionName, $templateFilename, $providerClassName, $contentType, $pluginName, $controllerActionName] = $queuedRegistration;
            try {
                $contentType = $contentType ?: static::determineContentType($providerExtensionName, $templateFilename);
                $defaultControllerExtensionName = 'FluidTYPO3.Flux';
                $provider = $contentTypeBuilder->configureContentTypeFromTemplateFile(
                    $providerExtensionName,
                    $templateFilename,
                    $providerClassName ?? Provider::class,
                    $contentType,
                    $defaultControllerExtensionName,
                    $controllerActionName
                );

                Core::registerConfigurationProvider($provider);

                $splitContentType = explode('_', $contentType, 2);
                $pluginName = $pluginName ?: GeneralUtility::underscoredToUpperCamelCase(end($splitContentType));
                $contentTypeBuilder->registerContentType($providerExtensionName, $contentType, $provider, $pluginName);
            } catch (Exception $error) {
                if (!ContextUtility::getApplicationContext()->isProduction()) {
                    throw $error;
                }
            }
        }
    }

    protected static function getContentTypeBuilder(): ContentTypeBuilder
    {
        if (static::$contentTypeBuilder === null) {
            /** @var ContentTypeBuilder $contentTypeBuilder */
            $contentTypeBuilder = GeneralUtility::makeInstance(ContentTypeBuilder::class);
            static::$contentTypeBuilder = $contentTypeBuilder;
        }
        return static::$contentTypeBuilder;
    }

    protected function getContentTypeManager(): ContentTypeManager
    {
        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
        return $contentTypeManager;
    }
}
