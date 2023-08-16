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
use FluidTYPO3\Flux\Hooks\HookHandler;

abstract class AbstractFormContainer extends AbstractFormComponent implements ContainerInterface
{
    /**
     * @var FormInterface[]|\SplObjectStorage
     */
    protected iterable $children;

    protected bool $inherit = true;
    protected bool $inheritEmpty = false;

    public function __construct()
    {
        $this->children = new \SplObjectStorage();
    }

    public function createComponent(
        ?string $namespace,
        string $type,
        string $name,
        ?string $label = null
    ): FormInterface {
        $component = parent::createComponent($namespace, $type, $name, $label);
        $this->add($component);
        return $component;
    }

    public function add(FormInterface $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->attach($child);
            $child->setParent($this);
            if ($child->getTransform()) {
                $root = $this->getRoot();
                if ($root instanceof Form) {
                    $root->setOption(FormOption::TRANSFORM, true);
                }
            }
        }
        HookHandler::trigger(HookHandler::FORM_CHILD_ADDED, ['parent' => $this, 'child' => $child]);
        return $this;
    }

    /**
     * @param FormInterface[] $children
     */
    public function addAll(iterable $children): self
    {
        foreach ($children as $child) {
            $this->add($child);
        }
        return $this;
    }

    /**
     * @param FieldInterface|string $childName
     */
    public function remove($childName): ?FormInterface
    {
        foreach ($this->children as $child) {
            /** @var FieldInterface $child */
            $isMatchingInstance = ($childName instanceof FormInterface && $childName->getName() === $child->getName());
            $isMatchingName = ($childName === $child->getName());
            if ($isMatchingName || $isMatchingInstance) {
                $this->children->detach($child);
                $this->children->rewind();
                $child->setParent(null);
                HookHandler::trigger(HookHandler::FORM_CHILD_REMOVED, ['parent' => $this, 'child' => $child]);
                return $child;
            }
        }
        return null;
    }

    /**
     * @param FormInterface|string $childOrChildName
     */
    public function has($childOrChildName): bool
    {
        $name = ($childOrChildName instanceof FormInterface)
            ? (string) $childOrChildName->getName()
            : (string) $childOrChildName;
        return (null !== $this->get($name));
    }

    public function get(string $childName, bool $recursive = false, ?string $requiredClass = null): ?FormInterface
    {
        foreach ($this->children as $index => $existingChild) {
            /** @var string|int $index */
            if (($childName === $existingChild->getName() || $childName === $index)
                && (!$requiredClass || $existingChild instanceof $requiredClass)
            ) {
                return $existingChild;
            }
            if (true === $recursive && true === $existingChild instanceof ContainerInterface) {
                $candidate = $existingChild->get($childName, $recursive, $requiredClass);
                if ($candidate instanceof FormInterface) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    /**
     * @return FormInterface[]|\SplObjectStorage
     */
    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function last(): ?FormInterface
    {
        $asArray = iterator_to_array($this->children);
        $result = array_pop($asArray);
        return $result;
    }

    public function hasChildren(): bool
    {
        return 0 < $this->children->count();
    }

    public function modify(array $structure): self
    {
        foreach ($structure['children'] ?? $structure['fields'] ?? [] as $index => $childData) {
            $childName = $childData['name'] ?? (string) $index;
            // check if field already exists - if it does, modify it. If it does not, create it.

            if ($this->has($childName)) {
                /** @var FormInterface $child */
                $child = $this->get($childName);
            } else {
                /** @var class-string $type */
                $type = $childData['type'] ?? Form\Field\None::class;
                /** @var FormInterface $child */
                $child = $this->createComponent('', $type, $childName);
            }

            $child->modify($childData);
        }
        unset($structure['children'], $structure['fields']);

        /** @var self $fromParentMethodCall */
        $fromParentMethodCall = parent::modify($structure);

        return $fromParentMethodCall;
    }
}
