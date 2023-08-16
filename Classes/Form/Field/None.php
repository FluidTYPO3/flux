<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\FieldInterface;

class None extends AbstractFormField implements FieldInterface
{
    protected int $size = 12;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('none');
        $configuration['size'] = $this->getSize();
        return $configuration;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
