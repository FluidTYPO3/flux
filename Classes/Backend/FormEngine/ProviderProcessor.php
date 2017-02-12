<?php
namespace FluidTYPO3\Flux\Backend\FormEngine;

use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class ProviderProcessor implements FormDataProviderInterface
{

    /**
     * @param array $result
     * @return array
     */
    public function addData(array $result)
    {
        $providers = $this->getProviderResolver()->resolveConfigurationProviders(
            $result['tableName'],
            null,
            $result['databaseRow']
        );
        foreach ($providers as $provider) {
            $result = $provider->processTableConfiguration($result['databaseRow'], $result);
        }
        return $result;
    }

    /**
     * @return ProviderResolver
     */
    protected function getProviderResolver()
    {
        return $this->getObjectManager()->get(ProviderResolver::class);
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}
