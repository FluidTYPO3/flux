<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Content Type Manager
 *
 * Handles registration and resolving of ContentTypeDefinition
 * instances for content records and content type names.
 */
class ContentTypeManager implements SingletonInterface
{
    const CACHE_IDENTIFIER_PREFIX = 'flux_content_types_';
    const CACHE_IDENTIFIER = 'flux_content_types';
    const CACHE_TAG = 'content_types';

    /**
     * @var ContentTypeDefinitionInterface[]
     *
     */
    protected $types = [];

    public function __construct()
    {
        $this->types = $this->loadFromCache();
    }

    /**
     * @return ContentTypeDefinitionInterface[][]
     */
    public function fetchContentTypes(): iterable
    {
        static $types = [];
        if (empty($types)) {
            foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extensionKey) {
                $expectedContentTypesDefinitionFile = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/Configuration/Flux/ContentTypes.php');
                if (file_exists($expectedContentTypesDefinitionFile)) {
                    /** @var ContentTypeDefinitionInterface[] $types */
                    $types[$extensionKey] = include $expectedContentTypesDefinitionFile;
                }
            }
        }
        return $types;
    }

    public function fetchContentTypesFromExtension(string $extensionKey): iterable
    {
        return $this->fetchContentTypes()[$extensionKey] ?? [];
    }

    public function registerTypeDefinition(ContentTypeDefinitionInterface $typeDefinition): void
    {
        $this->types[$typeDefinition->getContentTypeName()] = $typeDefinition;
    }

    public function determineContentTypeForTypeString(string $contentTypeName): ?ContentTypeDefinitionInterface
    {
        return $this->types[$contentTypeName] ?? ($this->types[$contentTypeName] = $this->loadSingleDefinitionFromCache($contentTypeName));
    }

    public function determineContentTypeForRecord(array $record): ?ContentTypeDefinitionInterface
    {
        return $this->determineContentTypeForTypeString($record['CType'] ?? $record['content_type'] ?? '');
    }

    protected function loadSingleDefinitionFromCache(string $name): ?ContentTypeDefinitionInterface
    {
        try {
            return $this->getCache()->get(static::CACHE_IDENTIFIER_PREFIX . $name) ?: null;
        } catch (NoSuchCacheException $error) {
            return null;
        }
    }

    protected function loadFromCache(): array
    {
        try {
            $cache = $this->getCache();
            $fromCache = $cache->get(static::CACHE_IDENTIFIER);
            if (empty($fromCache)) {
                $this->regenerate();
                return (array) $cache->get(static::CACHE_IDENTIFIER);
            }
            return $fromCache ?: [];
        } catch (NoSuchCacheException $error) {
            return [];
        }
    }

    public function regenerate()
    {
        $cache = $this->getCache();
        $cache->set(static::CACHE_IDENTIFIER, $this->types);
    }

    protected function getCache(): FrontendInterface
    {
        try {
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            return $cacheManager->getCache('flux');
        } catch (NoSuchCacheException $error) {
            $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
        }
        return $cacheManager->getCache('flux');
    }
}
