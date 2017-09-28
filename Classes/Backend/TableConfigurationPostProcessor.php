<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Helper\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
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
     * @param array $parameters
     * @return void
     */
    public function includeStaticTypoScriptHook(array $parameters, TemplateService $caller)
    {
        static $called = false;
        if (!$called) {
            $this->processData();
            $called = true;
        }
    }

    /**
     * @return void
     */
    public function processData()
    {
        $this->spoolQueuedContentTypeRegistrations(Core::getQueuedContentTypeRegistrations());
    }

    /**
     * @param array $queue
     * @return void
     */
    public static function spoolQueuedContentTypeTableConfigurations(array $queue)
    {
        $contentTypeBuilder = new ContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            list ($providerExtensionName, $templatePathAndFilename) = $queuedRegistration;
            $contentType = static::determineContentType($providerExtensionName, $templatePathAndFilename);
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
        if (!static::controllerExistsInExtension($providerExtensionName, 'Content')) {
            $controllerExtensionName = 'FluidTYPO3.Flux';
        }
        $emulatedPluginName = ucfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($controllerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        return $fullContentType;
    }

    /**
     * @param string $providerExtensionName
     * @param string $controllerName
     * @return boolean
     */
    protected static function controllerExistsInExtension($providerExtensionName, $controllerName)
    {
        $controllerClassName = str_replace('.', '\\', $providerExtensionName) . '\\Controller\\' . $controllerName . 'Controller';
        return class_exists($controllerClassName);
    }

    /**
     * @param array $queue
     * @return void
     */
    protected function spoolQueuedContentTypeRegistrations(array $queue)
    {
        $contentTypeBuilder = new ContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            /** @var ProviderInterface $provider */
            list ($providerExtensionName, $templateFilename) = $queuedRegistration;
            try {
                $provider = $contentTypeBuilder->configureContentTypeFromTemplateFile(
                    $providerExtensionName,
                    $templateFilename
                );

                Core::registerConfigurationProvider($provider);

                $controllerExtensionName = $providerExtensionName;
                if (!static::controllerExistsInExtension($providerExtensionName, 'Content')) {
                    $controllerExtensionName = 'FluidTYPO3.Flux';
                }

                $contentType = static::determineContentType($providerExtensionName, $templateFilename);
                $pluginName = ucfirst(pathinfo($templateFilename, PATHINFO_FILENAME));
                $contentTypeBuilder->registerContentType($controllerExtensionName, $contentType, $provider, $pluginName);

            } catch (Exception $error) {
                if (!Bootstrap::getInstance()->getApplicationContext()->isProduction()) {
                    throw $error;
                }
                GeneralUtility::sysLog(
                    sprintf(
                        'Template %s count not be used as content type: %s',
                        $templateFilename,
                        $error->getMessage()
                    ),
                    'flux'
                );
            }
        }
    }
}
