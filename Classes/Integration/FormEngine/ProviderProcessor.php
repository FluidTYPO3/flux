<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProviderProcessor implements FormDataProviderInterface
{
    public function addData(array $result): array
    {
        if (!isset($result['tableName'])) {
            throw new \UnexpectedValueException('Input data requires a "tableName" property', 1666816552);
        }
        if (!isset($result['databaseRow'])) {
            throw new \UnexpectedValueException('Input data requires a "databaseRow" property', 1666816552);
        }
        if ($result['tableName'] === 'tt_content') {
            $pageUid = $result['parentPageRow']['uid'];
            if ($pageUid > 0) {
                /** @var SiteFinder $siteFinder */
                $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
                $enabledContentTypes = [];
                try {
                    $site = $siteFinder->getSiteByPageId($pageUid);
                    $siteConfiguration = $site->getConfiguration();
                    $enabledContentTypes = GeneralUtility::trimExplode(
                        ',',
                        $siteConfiguration['flux_content_types'] ?? '',
                        true
                    );
                } catch (SiteNotFoundException $exception) {
                    // Suppressed; sites not being found isn't a fatal problem here.
                }
                if (!empty($enabledContentTypes)) {
                    /** @var ContentTypeManager $contentTypeManager */
                    $contentTypeManager = GeneralUtility::makeInstance(ContentTypeManager::class);
                    $fluidContentTypeNames = (array) $contentTypeManager->fetchContentTypeNames();
                    $currentItems = $result['processedTca']['columns']['CType']['config']['items'];
                    foreach ($currentItems as $index => $optionArray) {
                        $contentTypeName = $optionArray[1];
                        if (in_array($contentTypeName, $fluidContentTypeNames, true)
                            && !in_array($contentTypeName, $enabledContentTypes, true)
                        ) {
                            unset($result['processedTca']['columns']['CType']['config']['items'][$index]);
                        }
                    }
                }
            }
        }

        $resolver = $this->getProviderResolver();
        $providers = $resolver->resolveConfigurationProviders(
            $result['tableName'],
            null,
            $result['databaseRow'],
            null,
            [DataStructureProviderInterface::class]
        );
        foreach ($providers as $provider) {
            $result = $provider->processTableConfiguration($result['databaseRow'], $result);
        }
        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function loadRecord(string $table, int $uid): array
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('*')
            ->from($table)
            ->where($queryBuilder->expr()->eq('uid', $uid))
            ->setMaxResults(1);
        $query->getRestrictions()->removeAll();
        /** @var array $results */
        $results = $query->execute()->fetchAll();
        return $results[0] ?? [];
    }

    /**
     * @return ProviderResolver
     * @codeCoverageIgnore
     */
    protected function getProviderResolver()
    {
        /** @var ProviderResolver $providerResolver */
        $providerResolver = GeneralUtility::makeInstance(ProviderResolver::class);
        return $providerResolver;
    }
}
