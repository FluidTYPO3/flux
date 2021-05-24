<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;

trait DataAccessTrait {

    /**
     * @var ConfigurationManagerInterface;
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings;

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
        if (!ExtensionConfigurationUtility::getOption(ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
            return;
        }
        $contentObject = $this->configurationManager->getContentObject();
        $table = $this->fluxTableName ?? $contentObject->getCurrentTable();
        $field = $this->fluxRecordField ?? 'pi_flexform';
        $record = $contentObject->data;
        $implementations = ImplementationRegistry::resolveImplementations($table, $field, $record);
        $data = [];
        foreach ($implementations as $implementation) {
            $data = $implementation->getConverterForTableFieldAndRecord($table, $field, $record)->convertData($data);
        }
        $providerResolver = GeneralUtility::makeInstance(ObjectManager::class)->get(ProviderResolver::class);
        $provider = $providerResolver->resolvePrimaryConfigurationProvider($table, $field, $record);
        $form = $provider instanceof FormProviderInterface ? $provider->getForm($record) : null;
        if ($form) {
            $transformer = GeneralUtility::makeInstance(ObjectManager::class)->get(FormDataTransformer::class);
            $data = $transformer->transformAccordingToConfiguration($data, $form);
        }

        $this->settings = array_merge($this->settings, $data['settings'] ?? []);
    }
}
