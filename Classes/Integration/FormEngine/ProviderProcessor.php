<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use FluidTYPO3\Flux\Content\ContentTypeManager;
use FluidTYPO3\Flux\Provider\Interfaces\DataStructureProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ProviderProcessor implements FormDataProviderInterface
{
    private ProviderResolver $resolver;
    private ContentTypeManager $contentTypeManager;
    private SiteFinder $siteFinder;

    public function __construct(
        ProviderResolver $resolver,
        ContentTypeManager $contentTypeManager,
        SiteFinder $siteFinder
    ) {
        $this->resolver = $resolver;
        $this->contentTypeManager = $contentTypeManager;
        $this->siteFinder = $siteFinder;
    }

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
                $enabledContentTypes = [];
                try {
                    $site = $this->siteFinder->getSiteByPageId($pageUid);
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
                    $fluidContentTypeNames = (array) $this->contentTypeManager->fetchContentTypeNames();
                    $currentItems = $result['processedTca']['columns']['CType']['config']['items'];
                    foreach ($currentItems as $index => $optionArray) {
                        $contentTypeName = $optionArray['value'] ?? $optionArray[1];
                        if (in_array($contentTypeName, $fluidContentTypeNames, true)
                            && !in_array($contentTypeName, $enabledContentTypes, true)
                        ) {
                            unset($result['processedTca']['columns']['CType']['config']['items'][$index]);
                        }
                    }
                }
            }
        }

        /** @var DataStructureProviderInterface[] $providers */
        $providers = $this->resolver->resolveConfigurationProviders(
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
}
