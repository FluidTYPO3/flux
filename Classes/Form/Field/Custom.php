<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use FluidTYPO3\Flux\UserFunction\HtmlOutput;

/**
 * Custom
 */
class Custom extends UserFunction
{

    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $fieldConfiguration = $this->prepareConfiguration('user');
        $fieldConfiguration['userFunc'] = HtmlOutput::class . '->renderField';
        $fieldConfiguration['parameters'] = [
            'closure' => $this->getClosure(),
            'arguments' => $this->getArguments(),
        ];
        return $fieldConfiguration;
    }

    /**
     * @param \Closure $closure
     * @return Custom
     */
    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;
        return $this;
    }

    /**
     * @return \Closure|NULL
     */
    public function getClosure()
    {
        return $this->closure;
    }
}
