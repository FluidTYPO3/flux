<?php
namespace FluidTYPO3\Flux\Form;

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
