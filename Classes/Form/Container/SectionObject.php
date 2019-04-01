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
use FluidTYPO3\Flux\Form\Field\ColumnPosition;
use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Form\FieldContainerInterface;
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * Section Object
 *
 * Creates TCEForms objects inside Section. Also facilitates creating
 * a grid where each object becomes a content column - to enable this,
 * call setContentContainer(true) on the object.
 *
 * Content areas created from section objects can be rendered as either
 * columns or rows - this behavior can be defined on the section parent
 * object. The default is to render as rows.
 */
class SectionObject extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface
{
    /**
     * @var bool
     */
    protected $contentContainer = false;

    /**
     * @return array
     */
    public function build()
    {
        if ($this->contentContainer && !$this->has(ColumnPosition::FIELD_NAME)) {
            $this->createContentContainerFields();
        }
        $label = $this->getLabel();
        $structureArray = [
            'title' => $label,
            'type' => 'array',
            'el' => $this->buildChildren($this->children)
        ];
        return $structureArray;
    }

    /**
     * @return bool
     */
    public function isContentContainer()
    {
        return $this->contentContainer;
    }

    /**
     * @param bool $contentContainer
     */
    public function setContentContainer($contentContainer)
    {
        $this->contentContainer = (bool) $contentContainer;
        if ($this->contentContainer && !$this->has(ColumnPosition::FIELD_NAME)) {
            $this->createContentContainerFields();
        }
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields()
    {
        return (array) iterator_to_array($this->children);
    }

    protected function createContentContainerFields()
    {
        $this->createField(ColumnPosition::class, ColumnPosition::FIELD_NAME);
        $this->createField(Input::class, 'label', 'Content area name/label');
        if ($this->parent instanceof Section && $this->parent->getGridMode() === Section::GRID_MODE_COLUMNS) {
            $colSpanField = $this->createField(Select::class, 'colspan', 'Width of column');
            $colSpanField->setItems(array_combine(range(1, 12), range(1, 12)));
        }
    }
}
