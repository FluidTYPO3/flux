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
use FluidTYPO3\Flux\Utility\RequestBuilder;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3Fluid\Fluid\Exception;

class SpooledConfigurationApplicator
{
    public function __construct(private ConfigurationContext $context)
    {
    }

    protected static ?ContentTypeBuilder $contentTypeBuilder = null;

    public function processData(): void
    {
        $this->context->setBootMode(true);

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

        $this->context->setBootMode(false);
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

        $backup = $GLOBALS['TYPO3_REQUEST'] ?? null;

        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.3', '<')) {
            /** @var RequestBuilder $requestBuilder */
            $requestBuilder = GeneralUtility::makeInstance(RequestBuilder::class);
            $GLOBALS['TYPO3_REQUEST'] = $requestBuilder->getServerRequest();
        }

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

            try {
                $contentTypeBuilder->registerContentType($providerExtensionName, $contentType, $provider);
            } catch (Exception $error) {
                if (!$applicationContext->isProduction()) {
                    throw $error;
                }
            }
        }

        $GLOBALS['TYPO3_REQUEST'] = $backup;
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
        $contentTypeBuilder = GeneralUtility::makeInstance(ContentTypeBuilder::class, true);
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
