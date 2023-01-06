<?php
namespace FluidTYPO3\Flux\Integration\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Exception;

class SpooledConfigurationApplicator
{
    protected static ?ContentTypeBuilder $contentTypeBuilder = null;

    public function processData(): void
    {
        // Initialize the TCA needed by "template as CType" integrations
        static::spoolQueuedContentTypeTableConfigurations(
            Core::getQueuedContentTypeRegistrations()
        );

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

    public static function spoolQueuedContentTypeTableConfigurations(array $queue): void
    {
        $contentTypeBuilder = static::getContentTypeBuilder();
        foreach ($queue as $queuedRegistration) {
            [$extensionName, $templatePathAndFilename, , $contentType] = $queuedRegistration;
            $contentType = $contentType ?: static::determineContentType($extensionName, $templatePathAndFilename);
            $contentTypeBuilder->addBoilerplateTableConfiguration($contentType);
        }
    }

    protected static function determineContentType(
        string $providerExtensionName,
        string $templatePathAndFilename
    ): string {
        // Determine which plugin name and controller action to emulate with this CType, base on file name.
        $controllerExtensionName = $providerExtensionName;
        $emulatedPluginName = ucfirst(pathinfo($templatePathAndFilename, PATHINFO_FILENAME));
        $extensionSignature = str_replace('_', '', ExtensionNamingUtility::getExtensionKey($controllerExtensionName));
        $fullContentType = $extensionSignature . '_' . strtolower($emulatedPluginName);
        return $fullContentType;
    }

    protected function spoolQueuedContentTypeRegistrations(array $queue): void
    {
        $applicationContext = $this->getApplicationContext();
        $contentTypeBuilder = static::getContentTypeBuilder();
        $providers = [];
        foreach ($queue as $queuedRegistration) {
            /** @var ProviderInterface $provider */
            [
                $providerExtensionName,
                $templateFilename,
                $providerClassName,
                $contentType,
                $pluginName,
                $controllerActionName
            ] = $queuedRegistration;
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

                $splitContentType = explode('_', $contentType, 2);
                $pluginName = GeneralUtility::underscoredToUpperCamelCase(end($splitContentType));

                $provider->setPluginName($pluginName);

                Core::registerConfigurationProvider($provider);

                $providers[] = $provider;
            } catch (Exception $error) {
                if (!$applicationContext->isProduction()) {
                    throw $error;
                }
            }
        }

        $self = $this;

        // We have to fake a ServerRequest here, since internally Extbase's ConfigurationManager will otherwise always
        // instance BackendConfigurationManager and hold on to that instance, which means that any subsequent code that
        // injects ConfigurationManager will contain a BackendConfigurationManager even in frontend context.
        // This results in various issues such as inability to correctly resolve the correct controller for an Extbase
        // plugin on requests that don't already have a cached version of Flux forms / contains dynamic Flux forms.
        $GLOBALS['TYPO3_REQUEST'] = $GLOBALS['TYPO3_REQUEST'] ?? (new ServerRequest())->withAttribute(
            'applicationType',
            SystemEnvironmentBuilder::REQUESTTYPE_FE
        );

        uasort(
            $providers,
            function (ProviderInterface $item1, ProviderInterface $item2) use ($self) {
                $form1 = $item1->getForm(['CType' => $item1->getContentObjectType()]);
                $form2 = $item2->getForm(['CType' => $item2->getContentObjectType()]);
                return $self->resolveSortingValue($form1) <=> $self->resolveSortingValue($form2);
            }
        );

        foreach ($providers as $provider) {
            $contentType = $provider->getContentObjectType();
            $virtualRecord = ['CType' => $contentType];
            $providerExtensionName = $provider->getExtensionKey($virtualRecord);
            $pluginName = $provider->getPluginName();

            try {
                $contentTypeBuilder->registerContentType($providerExtensionName, $contentType, $provider, $pluginName);
            } catch (Exception $error) {
                if (!$applicationContext->isProduction()) {
                    throw $error;
                }
            }
        }
    }

    private function resolveSortingValue(?Form $form): int
    {
        $sortingOptionValue = 0;
        if ($form instanceof Form\FormInterface) {
            $sortingOptionValue = $form->getOption(Form::OPTION_SORTING);
        }
        return !is_scalar($sortingOptionValue) ? 0 : (integer) $sortingOptionValue;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getApplicationContext(): ApplicationContext
    {
        return Environment::getContext();
    }

    /**
     * @codeCoverageIgnore
     */
    protected static function getContentTypeBuilder(): ContentTypeBuilder
    {
        /** @var ContentTypeBuilder $contentTypeBuilder */
        $contentTypeBuilder = GeneralUtility::makeInstance(ContentTypeBuilder::class);
        return $contentTypeBuilder;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getContentTypeManager(): ContentTypeManager
    {
        /** @var ContentTypeManager $contentTypeManager */
        $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
        return $contentTypeManager;
    }
}
