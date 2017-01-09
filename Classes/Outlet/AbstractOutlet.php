<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use FluidTYPO3\Flux\Outlet\Pipe\ViewAwarePipeInterface;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * ### Outlet Definition
 *
 * Defines one data outlet for a Fluid form. Each outlet
 * is updated with the information when the form is saved.
 */
abstract class AbstractOutlet implements OutletInterface
{

    /**
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var ViewInterface
     */
    protected $view;

    /**
     * @var PipeInterface[]
     */
    protected $pipesIn = [];

    /**
     * @var PipeInterface[]
     */
    protected $pipesOut = [];

    /**
     * @var OutletArgument[]
     */
    protected $arguments = [];

    /**
     * The validation results. This can be asked if the argument has errors.
     *
     * @var Result
     */
    protected $validationResults;

    /**
     * @param boolean $enabled
     * @return OutletInterface
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param PipeInterface[] $pipes
     * @return OutletInterface
     * @return void
     */
    public function setPipesIn(array $pipes)
    {
        $this->pipesIn = [];
        foreach ($pipes as $pipe) {
            $this->addPipeIn($pipe);
        }

        return $this;
    }

    /**
     * @return PipeInterface[]
     */
    public function getPipesIn()
    {
        return $this->pipesIn;
    }

    /**
     * @param PipeInterface[] $pipes
     * @return OutletInterface
     * @return void
     */
    public function setPipesOut(array $pipes)
    {
        $this->pipesOut = [];
        foreach ($pipes as $pipe) {
            $this->addPipeOut($pipe);
        }

        return $this;
    }

    /**
     * @return PipeInterface[]
     */
    public function getPipesOut()
    {
        return $this->pipesOut;
    }

    /**
     * @param PipeInterface $pipe
     * @return OutletInterface
     */
    public function addPipeIn(PipeInterface $pipe)
    {
        if (false === in_array($pipe, $this->pipesIn)) {
            array_push($this->pipesIn, $pipe);
        }

        return $this;
    }

    /**
     * @param PipeInterface $pipe
     * @return OutletInterface
     */
    public function addPipeOut(PipeInterface $pipe)
    {
        if (false === in_array($pipe, $this->pipesOut)) {
            array_push($this->pipesOut, $pipe);
        }

        return $this;
    }

    /**
     * @param mixed $data
     * @return OutletInterface
     */
    public function fill($data)
    {
        $this->validate($data);
        foreach ($this->pipesIn as $pipe) {
            if ($pipe instanceof ViewAwarePipeInterface) {
                $pipe->setView($this->view);
            }
            $data = $pipe->conduct($data);
        }
        $this->data = $data;

        return $this;
    }

    /**
     * @return mixed
     */
    public function produce()
    {
        $data = $this->data;
        foreach ($this->pipesOut as $pipe) {
            if ($pipe instanceof ViewAwarePipeInterface) {
                $pipe->setView($this->view);
            }
            $pipe->conduct($data);
        }

        return $data;
    }

    /**
     * @return ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param ViewInterface $view
     * @return OutletInterface
     */
    public function setView($view)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * @return OutletArgument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param OutletArgument[] $arguments
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param OutletArgument $argument
     * @return void
     */
    public function addArgument(OutletArgument $argument)
    {
        $this->arguments[] = $argument;
    }

    /**
     * Validate given $data based on configured argument validations
     *
     * @param array $data
     * @return Result
     */
    public function validate(array $data)
    {
        $this->validationResults = new Result();
        foreach ($this->arguments as $argument) {
            $argumentName = $argument->getName();
            $argument->setValue(isset($data[$argumentName]) ? $data[$argumentName] : null);
            if (!$argument->isValid()) {
                $this->validationResults->forProperty($argument->getName())->merge($argument->getValidationResults());
            }
        }

        return $this->validationResults;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        if ($this->validationResults === null) {
            return true;
        }

        return !$this->validationResults->hasErrors();
    }

    /**
     * @return Result Validation errors which have occurred.
     */
    public function getValidationResults()
    {
        return $this->validationResults;
    }
}
