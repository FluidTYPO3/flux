<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Form\FieldContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;
use FluidTYPO3\Flux\Form\FormInterface;

class Sheet extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface
{
    protected ?string $shortDescription = null;

    public function setShortDescription(?string $shortDescription): self
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->resolveLocalLanguageValueOfLabel($this->shortDescription, $this->getPath() . '.shortDescription');
    }

    public function getDescription(): ?string
    {
        return $this->resolveLocalLanguageValueOfLabel($this->description, $this->getPath() . '.description');
    }

    public function build(): array
    {
        $sheetStructArray = [
            'ROOT' => [
                'sheetTitle' => $this->getLabel(),
                'sheetDescription' => $this->getDescription(),
                'sheetShortDescr' => $this->getShortDescription(),
                'type' => 'array',
                'el' => $this->buildChildren($this->getFields())
            ]
        ];
        return $sheetStructArray;
    }

    /**
     * @return FormInterface[]
     */
    public function getFields(): iterable
    {
        $fields = [];
        foreach ($this->children as $child) {
            $isSectionOrContainer = (true === $child instanceof Section || true === $child instanceof Container);
            $isFieldEmulatorAndHasChildren = ($isSectionOrContainer && true === $child->hasChildren());
            $isActualField = (true === $child instanceof FieldInterface);
            $isNotInsideObject = (false === $child->isChildOfType('SectionObject'));
            $isNotInsideContainer = (false === $child->isChildOfType('Container'));
            if ($isFieldEmulatorAndHasChildren || ($isActualField && $isNotInsideObject && $isNotInsideContainer)) {
                $name = $child->getName();
                $fields[$name] = $child;
            }
        }
        return $fields;
    }
}
