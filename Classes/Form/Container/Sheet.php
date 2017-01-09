<?php
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

/**
 * Sheet
 */
class Sheet extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface
{

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $shortDescription;

    /**
     * @param string $shortDescription
     * @return self
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        return $this;
    }

    /**
     * @return string
     */
    public function getShortDescription()
    {
        return $this->resolveLocalLanguageValueOfLabel($this->shortDescription, $this->getPath() . '.shortDescription');
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->resolveLocalLanguageValueOfLabel($this->description, $this->getPath() . '.description');
    }

    /**
     * @return array
     */
    public function build()
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
     * @return \FluidTYPO3\Flux\Form\FieldInterface[]
     */
    public function getFields()
    {
        $fields = [];
        foreach ($this->children as $child) {
            if (true === $child->getEnabled()) {
                $isSectionOrContainer = (true === $child instanceof Section || true === $child instanceof Container);
                $isFieldEmulatorAndHasChildren = ($isSectionOrContainer && true === $child->hasChildren());
                $isActualField = (true === $child instanceof FieldInterface);
                $isNotInsideObject = (false === $child->isChildOfType('Object'));
                $isNotInsideContainer = (false === $child->isChildOfType('Container'));
                if ($isFieldEmulatorAndHasChildren || ($isActualField && $isNotInsideObject && $isNotInsideContainer)) {
                    $name = $child->getName();
                    $fields[$name] = $child;
                }
            }
        }
        return $fields;
    }
}
