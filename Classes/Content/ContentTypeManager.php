<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use FluidTYPO3\Flux\Content\TypeDefinition\ContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\DropInContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidFileBased\FluidFileBasedContentTypeDefinition;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
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
     * @var ContentTypeDefinitionInterface[]|null[]
     */
    protected array $types = [];

    /**
     * @var string[]
     */
    protected array $typeNames = [];

    /**
     * @return FluidRenderingContentTypeDefinitionInterface[]
     */
    public function fetchContentTypes(): iterable
    {
        static $types = [];
        if (empty($types)) {
            try {
                $types = array_replace(
                    $this->fetchDropInContentTypes(),
                    $this->fetchFileBasedContentTypes(),
                    $this->fetchRecordBasedContentTypes()
                );
                $this->typeNames = array_merge($this->typeNames, array_keys($types));
            } catch (DBALException $error) {
                // Suppress schema- or connection-related issues
            } catch (Exception $error) {
                // Suppress schema- or connection-related issues
            } catch (NoSuchCacheException $error) {
                // Suppress caches not yet initialized errors
            }
        }
        return $types;
    }

    public function fetchContentTypeNames(): iterable
    {
        return $this->typeNames;
    }

    public function registerTypeName(string $typeName): void
    {
        $this->typeNames[] = $typeName;
    }

    public function registerTypeDefinition(ContentTypeDefinitionInterface $typeDefinition): void
    {
        $this->types[$typeDefinition->getContentTypeName()] = $typeDefinition;
    }

    public function determineContentTypeForTypeString(string $contentTypeName): ?ContentTypeDefinitionInterface
    {
        return $this->types[$contentTypeName]
            ?? ($this->types[$contentTypeName] = $this->loadSingleDefinitionFromCache($contentTypeName));
    }

    public function determineContentTypeForRecord(array $record): ?ContentTypeDefinitionInterface
    {
        return $this->determineContentTypeForTypeString($record['CType'] ?? $record['content_type'] ?? '');
    }

    public function regenerate(): void
    {
        $cache = $this->getCache();
        $cache->set(static::CACHE_IDENTIFIER, $this->fetchContentTypes());
    }

    protected function loadSingleDefinitionFromCache(string $name): ?ContentTypeDefinitionInterface
    {
        try {
            /** @var ContentTypeDefinitionInterface|null $fromCache */
            $fromCache = $this->getCache()->get(static::CACHE_IDENTIFIER_PREFIX . $name) ?: null;
            return $fromCache;
        } catch (NoSuchCacheException $error) {
            return null;
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchDropInContentTypes(): array
    {
        return (array) DropInContentTypeDefinition::fetchContentTypes();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchFileBasedContentTypes(): array
    {
        return (array) FluidFileBasedContentTypeDefinition::fetchContentTypes();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function fetchRecordBasedContentTypes(): array
    {
        return (array) RecordBasedContentTypeDefinition::fetchContentTypes();
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getCache(): FrontendInterface
    {
        try {
            /** @var CacheManager $cacheManager */
            $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
            return $cacheManager->getCache('flux');
        } catch (NoSuchCacheException $error) {
            $cacheManager->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
            try {
                $cache = $cacheManager->getCache('flux');
            } catch (NoSuchCacheException $error) {
                $cache = $cacheManager->getCache('runtime');
            }
        }
        return $cache;
    }
}
