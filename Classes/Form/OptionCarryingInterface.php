<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface OptionCarryingInterface
{
    public function setOptions(array $options): self;
    public function getOptions(): array;
    public function hasOption(string $name): bool;

    /**
     * @param mixed $value
     */
    public function setOption(string $name, $value): self;

    /**
     * @return mixed
     */
    public function getOption(string $name);
}
