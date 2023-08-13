<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Transformation;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Hooks\HookHandler;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

class FormDataTransformer
{
    private FileRepository $fileRepository;
    private FlexFormService $flexFormService;

    public function __construct(FileRepository $fileRepository, FlexFormService $flexFormService)
    {
        $this->fileRepository = $fileRepository;
        $this->flexFormService = $flexFormService;
    }

    /**
     * Parses the flexForm content and converts it to an array
     * The resulting array will be multi-dimensional, as a value "bla.blubb"
     * results in two levels, and a value "bla.blubb.bla" results in three levels.
     *
     * Note: multi-language flexForms are not supported yet
     *
     * @param string $flexFormContent flexForm xml string
     * @param Form $form An instance of \FluidTYPO3\Flux\Form. If transformation instructions are contained in this
     *                   configuration they are applied after conversion to array
     * @param string|null $languagePointer language pointer used in the flexForm
     * @param string|null $valuePointer value pointer used in the flexForm
     */
    public function convertFlexFormContentToArray(
        string $flexFormContent,
        Form $form = null,
        ?string $languagePointer = 'lDEF',
        ?string $valuePointer = 'vDEF'
    ): array {
        if (true === empty($flexFormContent)) {
            return [];
        }
        if (true === empty($languagePointer)) {
            $languagePointer = 'lDEF';
        }
        if (true === empty($valuePointer)) {
            $valuePointer = 'vDEF';
        }
        $settings = $this->flexFormService->convertFlexFormContentToArray(
            $flexFormContent,
            $languagePointer,
            $valuePointer
        );
        if (null !== $form && $form->getOption(Form::OPTION_TRANSFORM)) {
            $settings = $this->transformAccordingToConfiguration($settings, $form);
        }
        return $settings;
    }

    /**
     * Transforms members on $values recursively according to the provided
     * Flux configuration extracted from a Flux template. Uses "transform"
     * attributes on fields to determine how to transform values.
     */
    public function transformAccordingToConfiguration(array $values, Form $form, string $prefix = ''): array
    {
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $value = $this->transformAccordingToConfiguration($value, $form, $prefix . $index . '.');
            } else {
                /** @var FieldInterface|ContainerInterface $object */
                $object = $this->extractTransformableObjectByPath($form, $prefix . $index);
                if (is_object($object)) {
                    $transformType = $object->getTransform();

                    if ($transformType) {
                        $originalValue = $value;
                        $value = HookHandler::trigger(
                            HookHandler::VALUE_BEFORE_TRANSFORM,
                            [
                                'value' => $value,
                                'object' => $object,
                                'type' => $transformType,
                                'form' => $form
                            ]
                        )['value'];
                        if ($value === $originalValue) {
                            $value = $this->transformValueToType($value, $transformType, $prefix . $index, $form);
                        }
                        $value = HookHandler::trigger(
                            HookHandler::VALUE_AFTER_TRANSFORM,
                            [
                                'value' => $value,
                                'object' => $object,
                                'type' => $transformType,
                                'form' => $form
                            ]
                        )['value'];
                    }
                }
            }
            $values[$index] = $value;
        }
        return $values;
    }

    /**
     * @return mixed
     */
    protected function extractTransformableObjectByPath(ContainerInterface $subject, string $path)
    {
        $pathAsArray = explode('.', $path);
        $subPath = array_shift($pathAsArray);
        $child = null;
        while (count($pathAsArray)) {
            $child = $subject->get($subPath, $subject instanceof Form);
            if ($child) {
                if ($child instanceof Form\Container\Section) {
                    array_shift($pathAsArray);
                }
                if ($child instanceof ContainerInterface && count($pathAsArray)) {
                    return $this->extractTransformableObjectByPath($child, implode('.', $pathAsArray));
                }
            }
            $subPath .= '.' . array_shift($pathAsArray);
        }
        return $subject->get($path, true);
    }

    /**
     * Transforms a single value to $dataType
     *
     * @return mixed
     */
    protected function transformValueToType(string $value, string $dataType, string $fieldName, Form $form)
    {
        if (in_array($dataType, ['file', 'files', 'filereference', 'filereferences'], true)) {
            /** @var string $table */
            $table = $form->getOption(Form::OPTION_RECORD_TABLE);
            /** @var array $record */
            $record = $form->getOption(Form::OPTION_RECORD);
            $references = $this->fileRepository->findByRelation($table, $fieldName, $record['uid']);
            switch ($dataType) {
                case 'file':
                    if (!empty($references)) {
                        return $references[0]->getOriginalFile();
                    }
                    return null;
                case 'files':
                    $files = [];
                    foreach ($references as $reference) {
                        $files[] = $reference->getOriginalFile();
                    }
                    return $files;
                case 'filereference':
                    return $references[0] ?? null;
                case 'filereferences':
                    return $references;
            }
        } elseif ('int' === $dataType || 'integer' === $dataType) {
            return intval($value);
        } elseif ('float' === $dataType) {
            return floatval($value);
        } elseif ('array' === $dataType) {
            return explode(',', (string) $value);
        } elseif ('bool' === $dataType || 'boolean' === $dataType) {
            return boolval($value);
        } elseif (strpos($dataType, '->')) {
            /** @var class-string $class */
            [$class, $function] = explode('->', $dataType);
            /** @var object $object */
            $object = GeneralUtility::makeInstance($class);
            return $object->{$function}($value, $fieldName, $form);
        } else {
            return $this->getObjectOfType($dataType, $value);
        }
    }

    /**
     * Gets DomainObject(s) or instance of $dataType identified by, or constructed with parameter $uids
     *
     * @param string|class-string $dataType
     * @param string|array $uids
     * @return DomainObjectInterface|DomainObjectInterface[]|object|null
     */
    protected function getObjectOfType(string $dataType, $uids)
    {
        $identifiers = is_array($uids) ? $uids : GeneralUtility::trimExplode(',', trim($uids, ','), true);
        $identifiers = array_map('intval', $identifiers);
        $isModel = $this->isDomainModelClassName($dataType);
        if (false !== strpos($dataType, '<')) {
            /** @var class-string $container */
            /** @var class-string $object */
            [$container, $object] = explode('<', trim($dataType, '>'));
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
                $container = GeneralUtility::makeInstance($container);
                return $container;
            }
        }
        return null;
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
