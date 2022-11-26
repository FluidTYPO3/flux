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
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Provider\Provider;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
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
    protected static $contentTypeBuilder;

    protected static bool $recursed = false;

    /**
     * @param array $parameters
     * @return void
     */
    public function includeStaticTypoScriptHook(array $parameters, TemplateService $caller)
    {
        $property = new \ReflectionProperty($caller, 'extensionStaticsProcessed');
        $property->setAccessible(true);
        if (!$property->getValue($caller) && !static::$recursed) {
            // We store a recursion marker here. In some edge cases, the execution flow of parent methods will have set
            // the "extensionStaticsProcessed" property to false before calling this hook method, even though it has
            // already called the method once. Since $this->processData() also attempts to read TypoScript and TYPO3's
            // TemplateService then continuously keeps calling the hook method with "extensionStaticsProcessed" being
            // false, this triggers an infinite recursion. Therefore we record if the hook function has already been
            // called once and if so, we don't allow it to be called a second time (once-only execution is both expected
            // and desired).
            // This prevents a potential infinite recursion started from within TemplateService without causing any
            // negative side effects for non-edge cases. However, it also means that in this particular edge case, the
            // composition of registered Flux content types will not be possible to affect by third-party extension
            // static TS.
            // The only currently known edge case that causes this infinite recursion is when a frontend request is made
            // to a non-existing page that is recorded in the "redirects" module as a redirect. When the redirect has
            // been processed, the request to the target page is not affected by the potential infinite recursion. This
            // means that the negative side effect is only ever relevant at a point where no content rendering can take
            // place - and therefore, the negative impact of this is considered very marginal.
            static::$recursed = true;
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
            [$extensionName, $templatePathAndFilename, , $contentType] = $queuedRegistration;
            $contentType = $contentType ?: static::determineContentType($extensionName, $templatePathAndFilename);
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
        if ($form === null) {
            return 0;
        }
        $sortingOptionValue = $form->getOption(Form::OPTION_SORTING);
        if (!is_scalar($sortingOptionValue)) {
            return 0;
        }
        return (integer) $sortingOptionValue;
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
        if (static::$contentTypeBuilder === null) {
            /** @var ContentTypeBuilder $contentTypeBuilder */
            $contentTypeBuilder = GeneralUtility::makeInstance(ContentTypeBuilder::class);
            static::$contentTypeBuilder = $contentTypeBuilder;
        }
        return static::$contentTypeBuilder;
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
