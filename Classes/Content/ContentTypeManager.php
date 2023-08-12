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
use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Service\CacheService;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Content Type Manager
 *
 * Handles registration and resolving of ContentTypeDefinition
 * instances for content records and content type names.
 */
class ContentTypeManager implements SingletonInterface
{
    const CACHE_IDENTIFIER = 'flux_content_types';

    protected CacheService $cacheService;

    /**
     * @var ContentTypeDefinitionInterface[]
     */
    protected array $types = [];

    /**
     * @var string[]
     */
    protected array $typeNames = [];

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * @return ContentTypeDefinitionInterface[]
     */
    public function fetchContentTypes(): iterable
    {
        if (!empty($this->types)) {
            return $this->types;
        }
        try {
            /** @var ContentTypeDefinitionInterface[] $types */
            $types = $this->cacheService->getFromCaches(self::CACHE_IDENTIFIER);
            if (!$types) {
                $types = array_replace(
                    $this->fetchDropInContentTypes(),
                    $this->fetchFileBasedContentTypes(),
                    $this->fetchRecordBasedContentTypes()
                );
                $this->cacheService->setInCaches($types, true, self::CACHE_IDENTIFIER);
            }
            $this->typeNames = array_merge($this->typeNames, array_keys($types));
            $this->types = $types;
            return $this->types;
        } catch (DBALException $error) {
            // Suppress schema- or connection-related issues
        } catch (Exception $error) {
            // Suppress schema- or connection-related issues
        } catch (NoSuchCacheException $error) {
            // Suppress caches not yet initialized errors
        }
        return [];
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
        return $this->fetchContentTypes()[$contentTypeName] ?? null;
    }

    public function determineContentTypeForRecord(array $record): ?ContentTypeDefinitionInterface
    {
        return $this->determineContentTypeForTypeString($record['CType'] ?? $record['content_type'] ?? '');
    }

    public function regenerate(): void
    {
        $this->cacheService->remove(self::CACHE_IDENTIFIER);
        $this->cacheService->setInCaches($this->fetchContentTypes(), true, static::CACHE_IDENTIFIER);
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
}
