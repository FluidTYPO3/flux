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

class Input extends AbstractFormField implements FieldInterface
{
    const DEFAULT_VALIDATE = 'trim';

    protected int $size = 32;
    protected ?int $maxCharacters = null;
    protected ?int $minimum = null;
    protected ?int $maximum = null;
    protected ?string $placeholder = null;
    protected ?string $validate = self::DEFAULT_VALIDATE;

    public function buildConfiguration(): array
    {
        $minimum = $this->getMinimum();
        $maximum = $this->getMaximum();
        $validate = $this->getValidate();
        $configuration = $this->prepareConfiguration('input');
        $configuration['placeholder'] = $this->getPlaceholder();
        $configuration['size'] = $this->getSize();
        $configuration['max'] = $this->getMaxCharacters();
        $configuration['eval'] = $validate;
        if (null !== $minimum && null !== $maximum) {
            $configuration['range'] = [
                'lower' => $minimum,
                'upper' => $maximum
            ];
        }
        return $configuration;
    }

    public function setMaxCharacters(?int $maxCharacters): self
    {
        $this->maxCharacters = $maxCharacters;
        return $this;
    }

    public function getMaxCharacters(): ?int
    {
        return $this->maxCharacters;
    }

    public function setMaximum(?int $maximum): self
    {
        $this->maximum = $maximum;
        return $this;
    }

    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    public function setMinimum(?int $minimum): self
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
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
