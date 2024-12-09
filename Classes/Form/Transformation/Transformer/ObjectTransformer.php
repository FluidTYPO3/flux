<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Attribute\DataTransformer;
use FluidTYPO3\Flux\Form\FormInterface;
use FluidTYPO3\Flux\Form\Transformation\DataTransformerInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * Object Transformer
 */
#[DataTransformer('flux.datatransformer.object')]
class ObjectTransformer implements DataTransformerInterface
{
    public function canTransformToType(string $type): bool
    {
        return $this->isDomainModelClassName($type) || class_exists($type) || (
            class_exists($this->resolveContainerClassName($type)) &&
            class_exists($this->resolveContainedClassName($type))
        );
    }

    public function getPriority(): int
    {
        return 0;
    }

    /**
     * @param string|class-string $value
     * @return DomainObjectInterface|DomainObjectInterface[]|iterable|object|null
     */
    public function transform(FormInterface $component, string $type, $value)
    {
        return $this->getObjectOfType($type, $value);
    }

    /**
     * Gets DomainObject(s) or instance of $dataType identified by, or constructed with parameter $uids
     *
     * @param string|class-string $dataType
     * @param string|array|int $uids
     * @return DomainObjectInterface|DomainObjectInterface[]|object|null
     */
    protected function getObjectOfType(string $dataType, $uids)
    {
        $dataType = ltrim($dataType, '\\');
        $identifiers = is_array($uids) ? $uids : GeneralUtility::trimExplode(',', trim((string) $uids, ','), true);
        /** @var int[] $identifiers */
        $identifiers = array_map('intval', $identifiers);
        $isModel = $this->isDomainModelClassName($dataType);
        if (false !== strpos($dataType, '<')) {
            $container = $this->resolveContainerClassName($dataType);
            $object = $this->resolveContainedClassName($dataType);
        } else {
            $container = null;
            $object = $dataType;
        }
        $repositoryClassName = $this->resolveRepositoryClassName($object);
        // Fast decisions
        if ($isModel && null === $container) {
            if (class_exists($repositoryClassName)) {
                /** @var RepositoryInterface $repository */
                $repository = GeneralUtility::makeInstance($repositoryClassName);
                $repositoryObjects = $this->loadObjectsFromRepository($repository, $identifiers);
                /** @var DomainObjectInterface|false $firstRepositoryObject */
                $firstRepositoryObject = reset($repositoryObjects);
                return $firstRepositoryObject ?: null;
            }
        } elseif (class_exists($dataType)) {
            // using constructor value to support objects like DateTime
            return GeneralUtility::makeInstance($dataType, $uids);
        }
        // slower decisions with support for type-hinted collection objects
        if ($container && $object) {
            if ($isModel && class_exists($repositoryClassName) && count($identifiers) > 0) {
                /** @var RepositoryInterface $repository */
                $repository = GeneralUtility::makeInstance($repositoryClassName);
                return $this->loadObjectsFromRepository($repository, $identifiers);
            } else {
                $objects = [];
                foreach ($identifiers as $identifier) {
                    $objects[] = $this->getObjectOfType($object, $identifier);
                }
                $container = GeneralUtility::makeInstance($container, $objects);
                return $container;
            }
        }
        return null;
    }

    /**
     * @return class-string
     */
    protected function resolveContainerClassName(string $compoundType): string
    {
        /** @var class-string $class */
        $class = explode('<', trim($compoundType, '>'))[0] ?? 'invalid';
        return $class;
    }

    /**
     * @return class-string
     */
    protected function resolveContainedClassName(string $compoundType): string
    {
        /** @var class-string $class */
        $class = ltrim(explode('<', trim($compoundType, '>'))[1] ?? 'invalid', '\\');
        return $class;
    }

    protected function resolveRepositoryClassName(string $object): string
    {
        return str_replace('\\Domain\\Model\\', '\\Domain\\Repository\\', $object) . 'Repository';
    }

    protected function isDomainModelClassName(string $dataType): bool
    {
        return (false !== strpos($dataType, '\\Domain\\Model\\'));
    }

    /**
     * @return DomainObjectInterface[]
     */
    protected function loadObjectsFromRepository(RepositoryInterface $repository, array $identifiers): iterable
    {
        /** @var DomainObjectInterface[] $objects */
        $objects = array_map([$repository, 'findByUid'], $identifiers);
        return $objects;
    }
}
