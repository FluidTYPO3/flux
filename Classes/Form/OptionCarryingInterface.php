<?php
namespace FluidTYPO3\Flux\Form;

interface OptionCarryingInterface
{
    /**
     * @param array $options
     * @return self
     */
    public function setOptions(array $options);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name
     * @param mixed $value
     * @return self
     */
    public function setOption($name, $value);

    /**
     * @param string $name
     * @return mixed
     */
    public function getOption($name);

    /**
     * @param string $name
     * @return boolean
     */
    public function hasOption($name);
}
