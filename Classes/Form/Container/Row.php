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

class Row extends AbstractFormContainer implements ContainerInterface
{
    /**
     * @var Column[]|\SplObjectStorage
     */
    protected iterable $children;

    public function build(): array
    {
        $structure = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'columns' => $this->buildChildren($this->children)
        ];
        return $structure;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): iterable
    {
        return iterator_to_array($this->children);
    }
}
