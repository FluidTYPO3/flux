<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
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
        $resolver = $this->getProviderResolver();
        $providers = $resolver->resolveConfigurationProviders(
            $result['tableName'],
            null,
            $result['databaseRow'],
            null,
            DataStructureProviderInterface::class
        );
        foreach ($providers as $provider) {
            $result = $provider->processTableConfiguration($result['databaseRow'], $result);
        }
        return $result;
    }

    protected function loadRecord(string $table, int $uid): array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->setMaxResults(1);
        $query->getRestrictions()->removeAll();
        return $query->execute()->fetchAll()[0] ?? [];
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
