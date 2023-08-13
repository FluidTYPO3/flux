<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface FormInterface
{
    public function build(): array;
    public function setName(string $name): self;
    public function getName(): ?string;
    public function setTransform(?string $transform): self;
    public function getTransform(): ?string;
    public function getEnabled(): bool;
    public function setEnabled(bool $enabled): self;
    public function setLabel(?string $label): self;
    public function getLabel(): ?string;
    public function setDescription(?string $description): self;
    public function getDescription(): ?string;
    public function setLocalLanguageFileRelativePath(string $localLanguageFileRelativePath): self;
    public function getLocalLanguageFileRelativePath(): string;
    public function setDisableLocalLanguageLabels(bool $disableLocalLanguageLabels): self;
    public function getDisableLocalLanguageLabels(): bool;
    public function setVariables(array $variables): self;
    public function getVariables(): array;
    public function getPath(): string;
    public function setExtensionName(?string $extensionName): self;
    public function getExtensionName(): ?string;
    public function isChildOfType(string $type): bool;

    public function setInherit(bool $inherit): self;
    public function getInherit(): bool;
    public function setInheritEmpty(bool $inheritEmpty): self;
    public function getInheritEmpty(): bool;
    public function setParent(?FormInterface $parent): self;
    public function getParent(): ?FormInterface;

    /**
     * @param mixed $value
     */
    public function setVariable(string $name, $value): self;

    /**
     * @return mixed
     */
    public function getVariable(string $name);

    /**
     * @return ContainerInterface|FieldInterface
     */
    public function getRoot(): FormInterface;

    /**
     * @template T
     * @param class-string<T> $type
     * @return T&FieldInterface
     */
    public function createField(string $type, string $name, ?string $label = null): FieldInterface;

    /**
     * @template T
     * @param class-string<T> $type
     * @return T&ContainerInterface
     */
    public function createContainer(string $type, string $name, ?string $label = null): ContainerInterface;

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
    public function modify(array $structure): self;
}
