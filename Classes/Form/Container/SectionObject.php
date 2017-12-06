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
 * Object
 */
class SectionObject extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface
{

    /**
     * @return array
     */
    public function build()
    {
        $label = $this->getLabel();
        $structureArray = [
            'title' => $label,
            'type' => 'array',
            'el' => $this->buildChildren($this->children)
        ];
        return $structureArray;
    }

    /**
     * @return FieldInterface[]
     */
    public function getFields()
    {
        return (array) iterator_to_array($this->children);
    }
}
