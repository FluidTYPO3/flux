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
use FluidTYPO3\Flux\Form\FormInterface;

class Container extends AbstractFormContainer implements ContainerInterface, FieldContainerInterface
{
    public function build(): array
    {
        $structureArray = [
            'type' => 'array',
            'section' => '1',
            'title' => $this->getLabel(),
            'el' => $this->buildChildren($this->children)
        ];
        return $structureArray;
    }

    /**
     * @return FormInterface[]
     */
    public function getFields(): iterable
    {
        return iterator_to_array($this->children);
    }
}
