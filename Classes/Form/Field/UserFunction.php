<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * UserFunction
 */
class UserFunction extends AbstractFormField implements FieldInterface
{

    /**
     * @var array
     */
    protected $arguments = [];

    /**
     * @var string
     */
    protected $function;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('user');
        $configuration['userFunc'] = $this->getFunction();
        $configuration['arguments'] = $this->getArguments();
        return $configuration;
    }

    /**
     * @param string $function
     * @return UserFunction
     */
    public function setFunction($function)
    {
        $this->function = $function;
        return $this;
    }

    /**
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param array $arguments
     * @return UserFunction
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }
}
