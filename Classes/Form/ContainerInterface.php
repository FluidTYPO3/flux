<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * ContainerInterface
 */
interface ContainerInterface extends FormInterface
{
    public function get(string $childName, bool $recursive = false, ?string $requiredClass = null): ?FormInterface;

    /**
     * @return FormInterface[]|\SplObjectStorage
     */
    public function getChildren(): iterable;

    public function add(FormInterface $child): self;
    public function remove(string $childName): ?FormInterface;

    /**
     * @param FormInterface|string $childOrChildName
     */
    public function has($childOrChildName): bool;

    public function hasChildren(): bool;
}
