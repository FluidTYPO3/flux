<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Container;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Form\Container\SectionObject;
use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

abstract class AbstractFormComponent implements FormInterface
{
    const NAMESPACE_FIELD = 'FluidTYPO3\\Flux\\Form\\Field';
    const NAMESPACE_CONTAINER = 'FluidTYPO3\\Flux\\Form\\Container';

    protected ?string $name = null;
    protected bool $enabled = true;
    protected ?string $label = null;
    protected ?string $description = null;
    protected ?string $extensionName = 'FluidTYPO3.Flux';
    protected ?FormInterface $parent = null;
    protected array $variables = [];
    protected bool $inherit = false;
    protected bool $inheritEmpty = false;
    protected ?string $transform = null;

    /**
     * If TRUE, disables LLL label usage and always returns the
     * raw value of $label.
     */
    protected bool $disableLocalLanguageLabels = false;

    /**
     * Relative (from extension $extensionName) path to locallang
     * file containing labels for the LLL values built by this class.
     */
    protected string $localLanguageFileRelativePath = Form::DEFAULT_LANGUAGEFILE;

    public static function create(array $settings = []): FormInterface
    {
        $className = get_called_class();
        /** @var FormInterface $object */
        $object = GeneralUtility::makeInstance($className);
        $object->modify($settings);
        return HookHandler::trigger(HookHandler::FORM_COMPONENT_CREATED, ['component' => $object])['component'];
    }

    /**
     * @param string|class-string $type
     * @return class-string
     */
    protected function createComponentClassName(string $type, ?string $prefix): string
    {
        /** @var class-string $className */
        $className = str_replace('/', '\\', $type);
        $className = class_exists($prefix . '\\' . $className) ? $prefix . '\\' . $className : $className;
        /** @var class-string $className */
        $className = trim($className, '\\');
        return $className;
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return T&FieldInterface
     */
    public function createField(string $type, string $name, ?string $label = null): FieldInterface
    {
        /** @var T&FieldInterface $component */
        $component = $this->createComponent(static::NAMESPACE_FIELD, $type, $name, $label);
        return $component;
    }

    /**
     * @template T
     * @param class-string<T> $type
     * @return T&ContainerInterface
     */
    public function createContainer(string $type, string $name, ?string $label = null): ContainerInterface
    {
        /** @var T&ContainerInterface $component */
        $component = $this->createComponent(static::NAMESPACE_CONTAINER, $type, $name, $label);
        return $component;
    }

    public function createComponent(
        ?string $namespace,
        string $type,
        string $name,
        ?string $label = null
    ): FormInterface {
        /** @var FormInterface $component */
        $component = GeneralUtility::makeInstance($this->createComponentClassName($type, $namespace));
        $component->setName($name);
        $component->setLabel($label);
        $component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
        $component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
        $component->setExtensionName($this->getExtensionName());
        return HookHandler::trigger(HookHandler::FORM_COMPONENT_CREATED, ['component' => $component])['component'];
    }

    public function setTransform(?string $transform): self
    {
        $this->transform = $transform;
        if ($transform) {
            $root = $this->getRoot();
            if ($root instanceof Form) {
                $root->setOption(FormOption::TRANSFORM, true);
            }
        }
        return $this;
    }

    public function getTransform(): ?string
    {
        return $this->transform;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = (boolean) $enabled;
        return $this;
    }

    public function setExtensionName(?string $extensionName): self
    {
        $this->extensionName = $extensionName;
        return $this;
    }

    public function getExtensionName(): ?string
    {
        return $this->extensionName;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getPath(): string
    {
        $prefix = '';
        if ($this instanceof Sheet) {
            $prefix = 'sheets';
        } elseif ($this instanceof Section) {
            $prefix = 'sections';
        } elseif ($this instanceof Grid) {
            $prefix = 'grids';
        } elseif ($this instanceof Column) {
            $prefix = 'columns';
        } elseif ($this instanceof SectionObject) {
            $prefix = 'objects';
        } elseif ($this instanceof Container) {
            $prefix = 'containers';
        } elseif ($this instanceof FieldInterface) {
            if ($this->isChildOfType('SectionObject')) {
                /** @var SectionObject $parent */
                $parent = $this->getParent();
                $prefix = 'objects.' . $parent->getName();
            } else {
                $prefix = 'fields';
            }
        }
        return trim($prefix . '.' . $this->getName(), '.');
    }

    public function getLabel(): ?string
    {
        return $this->resolveLocalLanguageValueOfLabel($this->label);
    }

    protected function resolveLocalLanguageValueOfLabel(?string $label, ?string $path = null): ?string
    {
        if ($this->getDisableLocalLanguageLabels()) {
            return $label;
        }

        $name = $this->getName();
        $extensionName = (string) $this->getExtensionName();

        if (empty($extensionName) && empty($label)) {
            return $name;
        }

        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);

        if (strpos($label ?? '', 'LLL:EXT:') === 0) {
            return $label;
        }

        $relativeFilePath = $this->getLocalLanguageFileRelativePath();
        $relativeFilePath = ltrim($relativeFilePath, '/');
        $filePrefix = 'LLL:EXT:' . $extensionKey . '/' . $relativeFilePath;
        if (strpos($label ?? '', 'LLL:') === 0 && strpos($label ?? '', ':') !== false) {
            // Shorthand LLL:name.of.index reference, expand
            [, $labelIdentifier] = explode(':', $label, 2);
            return $filePrefix . ':' . $labelIdentifier;
        } elseif (!empty($label)) {
            return $label;
        }
        if ($this instanceof Form) {
            return $filePrefix . ':flux.' . $this->getName();
        }
        $root = $this->getRoot();
        $id = $root->getName();
        if (empty($path)) {
            $path = $this->getPath();
        }
        return $filePrefix . ':' . trim('flux.' . $id . '.' . $path, '.');
    }

    public function setLocalLanguageFileRelativePath(string $localLanguageFileRelativePath): self
    {
        $this->localLanguageFileRelativePath = $localLanguageFileRelativePath;
        return $this;
    }

    public function getLocalLanguageFileRelativePath(): string
    {
        return $this->localLanguageFileRelativePath;
    }

    public function setDisableLocalLanguageLabels(bool $disableLocalLanguageLabels): self
    {
        $this->disableLocalLanguageLabels = (boolean) $disableLocalLanguageLabels;
        return $this;
    }

    public function getDisableLocalLanguageLabels(): bool
    {
        return $this->disableLocalLanguageLabels;
    }

    public function setParent(?FormInterface $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getParent(): ?FormInterface
    {
        return $this->parent;
    }

    public function setVariables(array $variables): self
    {
        $this->variables = (array) $variables;
        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @param mixed $value
     */
    public function setVariable(string $name, $value): self
    {
        $this->variables = RecursiveArrayUtility::mergeRecursiveOverrule(
            $this->variables,
            RecursiveArrayUtility::convertPathToArray($name, $value)
        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVariable(string $name)
    {
        return ObjectAccess::getPropertyPath($this->variables, $name);
    }

    /**
     * @return ContainerInterface|FormInterface|$this
     */
    public function getRoot(): FormInterface
    {
        $parent = $this->getParent();
        if (null === $parent || $this === $parent) {
            return $this;
        }
        return $parent->getRoot();
    }

    public function isChildOfType(string $type): bool
    {
        $parent = $this->getParent();
        if ($parent === null) {
            return false;
        }
        return (static::NAMESPACE_CONTAINER . '\\' . $type === get_class($parent) || is_a($parent, $type));
    }

    public function setInherit(bool $inherit): self
    {
        $this->inherit = $inherit;
        return $this;
    }

    public function getInherit(): bool
    {
        return $this->inherit;
    }

    public function setInheritEmpty(bool $inheritEmpty): self
    {
        $this->inheritEmpty = $inheritEmpty;
        return $this;
    }

    public function getInheritEmpty(): bool
    {
        return $this->inheritEmpty;
    }

    /**
     * Modifies the current Form Component by changing any properties
     * that were passed in $structure. If a component supports special
     * indices in $structure (for example a "fields" property) then
     * that component may specify its own `modify()` method and manually
     * process each of the specially supported keywords.
     *
     * For example, the AbstractFormContainer supports passing "fields"
     * and each field is then attempted fetched from children. If not
     * found, it is created (and the structure passed to the `create()`
     * function which uses the same structure syntax). If it already
     * exists, the `modify()` method is called on that object to trigger
     * the recursive modification of all child components.
     */
    public function modify(array $structure): self
    {
        if (isset($structure['options']) && is_array($structure['options'])) {
            foreach ($structure['options'] as $name => $value) {
                $this->setVariable($name, $value);
            }
            unset($structure['options']);
        }
        foreach ($structure as $propertyName => $propertyValue) {
            $setterMethodName = 'set' . ucfirst($propertyName);
            if (method_exists($this, $setterMethodName)) {
                /** @var \ReflectionParameter|null $parameterReflection */
                $parameterReflection = (new \ReflectionMethod($this, $setterMethodName))->getParameters()[0] ?? null;
                if ($parameterReflection === null) {
                    continue;
                }
                /** @var \ReflectionNamedType $typeReflection */
                $typeReflection = $parameterReflection->getType();
                if ($typeReflection) {
                    switch ($typeReflection->getName()) {
                        case 'bool':
                            $propertyValue = (bool) $propertyValue;
                            break;
                        case 'int':
                            $propertyValue = (integer) $propertyValue;
                            break;
                        case 'array':
                            $propertyValue = is_array($propertyValue)
                                ? $propertyValue
                                : GeneralUtility::trimExplode(',', $propertyValue, true);
                            break;
                    }
                }

                $this->{$setterMethodName}($propertyValue);
            }
        }
        HookHandler::trigger(
            HookHandler::FORM_COMPONENT_MODIFIED,
            ['component' => $this, 'modififications' => $structure]
        );
        return $this;
    }

    /**
     * @param \SplObjectStorage|FormInterface[]|array $children
     */
    protected function buildChildren(iterable $children): array
    {
        $structure = [];
        foreach ($children as $child) {
            if (!$child->getEnabled() || $child instanceof FieldInterface && $child->isNative()) {
                continue;
            }
            $name = $child->getName();
            $structure[$name] = $child->build();
        }
        return $structure;
    }
}
