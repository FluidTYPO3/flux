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

class Column extends AbstractFormContainer implements ContainerInterface
{
    protected int $columnPosition = 0;
    protected int $colspan = 1;
    protected int $rowspan = 1;
    protected ?string $style = null;

    public function build(): array
    {
        $structure = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'colspan' => $this->getColspan(),
            'rowspan' => $this->getRowspan(),
            'style' => $this->getStyle(),
            'colPos' => $this->getColumnPosition()
        ];
        return $structure;
    }

    public function setColspan(?int $colspan): self
    {
        $this->colspan = $colspan ?? 1;
        return $this;
    }

    public function getColspan(): int
    {
        return $this->colspan;
    }

    public function setColumnPosition(int $columnPosition): self
    {
        $this->columnPosition = $columnPosition;
        return $this;
    }

    public function getColumnPosition(): int
    {
        return $this->columnPosition;
    }

    public function setRowspan(?int $rowspan): self
    {
        $this->rowspan = $rowspan ?? 1;
        return $this;
    }

    public function getRowspan(): int
    {
        return $this->rowspan;
    }

    public function setStyle(?string $style): self
    {
        $this->style = $style;
        return $this;
    }

    public function getStyle(): ?string
    {
        return $this->style;
    }
}
