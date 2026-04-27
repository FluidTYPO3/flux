<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\ExtensionOption;
use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ContentObjectFetcher;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

trait DataAccessTrait
{
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
        if (!ExtensionConfigurationUtility::getOption(ExtensionOption::OPTION_FLEXFORM_TO_IRRE)) {
            return;
        }

        $contentObject = ContentObjectFetcher::resolve($this->configurationManager);
        if ($contentObject === null) {
            throw new \UnexpectedValueException(
                "Record of table " . $this->getFluxTableName() . ' not found',
                1666538343
            );
        }
        $table = $this->fluxTableName ?? $contentObject->getCurrentTable();
        $field = $this->fluxRecordField ?? 'pi_flexform';
        $record = $contentObject->data;
        $implementations = ImplementationRegistry::resolveImplementations($table, $field, $record);
        $data = [];
        foreach ($implementations as $implementation) {
            $data = $implementation->getConverterForTableFieldAndRecord($table, $field, $record)->convertData($data);
        }
        /** @var ProviderResolver $providerResolver */
        $providerResolver = GeneralUtility::makeInstance(ProviderResolver::class);
        $provider = $providerResolver->resolvePrimaryConfigurationProvider($table, $field, $record);
        $form = $provider instanceof FormProviderInterface ? $provider->getForm($record) : null;
        if ($form) {
            /** @var FormDataTransformer $transformer */
            $transformer = GeneralUtility::makeInstance(FormDataTransformer::class);
            $data = $transformer->transformAccordingToConfiguration($data, $form);
        }

        $this->settings = array_merge($this->settings, $data['settings'] ?? []);
    }
}
