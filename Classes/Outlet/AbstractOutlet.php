<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use FluidTYPO3\Flux\Outlet\Pipe\ViewAwarePipeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3Fluid\Fluid\View\ViewInterface;

/**
 * ### Outlet Definition
 *
 * Defines one data outlet for a Fluid form. Each outlet
 * is updated with the information when the form is saved.
 */
abstract class AbstractOutlet implements OutletInterface
{
    protected bool $enabled = true;

    /**
     * @var mixed
     */
    protected $data = [];

    /**
     * @var PipeInterface[]
     */
    protected array $pipesIn = [];

    /**
     * @var PipeInterface[]
     */
    protected array $pipesOut = [];

    /**
     * @var OutletArgument[]
     */
    protected array $arguments = [];

    /**
     * @var ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    protected $view;

    /**
     * The validation results. This can be asked if the argument has errors.
     *
     * @var Result
     */
    protected $validationResults;

    public static function create(array $settings): OutletInterface
    {
        /** @var self $instance */
        $instance = GeneralUtility::makeInstance(static::class);
        if (isset($settings['pipesIn'])) {
            foreach ($settings['pipesIn'] as $pipeSettings) {
                /** @var class-string $pipeClassName */
                $pipeClassName = $pipeSettings['type'];
                /** @var PipeInterface $pipeIn */
                $pipeIn = static::createPipeInstance($pipeClassName, $pipeSettings);
                $instance->addPipeIn($pipeIn);
            }
        }
        if (isset($settings['pipesOut'])) {
            foreach ($settings['pipesOut'] as $pipeSettings) {
                /** @var class-string $pipeClassName */
                $pipeClassName = $pipeSettings['type'];
                /** @var PipeInterface $pipeOut */
                $pipeOut = static::createPipeInstance($pipeClassName, $pipeSettings);
                $instance->addPipeOut($pipeOut);
            }
        }
        return HookHandler::trigger(
            HookHandler::OUTLET_CREATED,
            [
                'outlet' => $instance
            ]
        )['outlet'];
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T&PipeInterface
     */
    protected static function createPipeInstance(string $class, array $settings): PipeInterface
    {
        /** @var class-string $class */
        /** @var T&PipeInterface $pipe */
        $pipe = GeneralUtility::makeInstance($class);
        foreach ($settings as $property => $value) {
            $setterMethod = 'set' . ucfirst($property);
            if (method_exists($pipe, $setterMethod)) {
                $pipe->{$setterMethod}($value);
            }
        }
        return $pipe;
    }

    /**
     * @return ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param ViewInterface|\TYPO3\CMS\Extbase\Mvc\View\ViewInterface $view
     */
    public function setView($view): self
    {
        $this->view = $view;
        return $this;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param PipeInterface[] $pipes
     */
    public function setPipesIn(array $pipes): self
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
    public function getPipesIn(): array
    {
        return $this->pipesIn;
    }

    /**
     * @param PipeInterface[] $pipes
     */
    public function setPipesOut(array $pipes): self
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
    public function getPipesOut(): array
    {
        return $this->pipesOut;
    }

    /**
     * @param PipeInterface $pipe
     */
    public function addPipeIn(PipeInterface $pipe): self
    {
        if (false === in_array($pipe, $this->pipesIn)) {
            array_push($this->pipesIn, $pipe);
        }

        return $this;
    }

    /**
     * @param PipeInterface $pipe
     */
    public function addPipeOut(PipeInterface $pipe): self
    {
        if (false === in_array($pipe, $this->pipesOut)) {
            array_push($this->pipesOut, $pipe);
        }

        return $this;
    }

    public function fill(array $data): self
    {
        $this->validate($data);
        foreach ($this->pipesIn as $pipe) {
            if ($pipe instanceof ViewAwarePipeInterface) {
                $pipe->setView($this->getView());
            }
            $data = $pipe->conduct($data);
        }
        $this->data = $data;

        return $this;
    }

    public function produce(): array
    {
        $data = $this->data;
        foreach ($this->pipesOut as $pipe) {
            if ($pipe instanceof ViewAwarePipeInterface) {
                $pipe->setView($this->view);
            }
            $pipe->conduct($data);
        }

        return HookHandler::trigger(
            HookHandler::OUTLET_EXECUTED,
            [
                'outlet' => $this,
                'data' => $data
            ]
        )['data'];
    }

    /**
     * @return OutletArgument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param OutletArgument[] $arguments
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function addArgument(OutletArgument $argument): self
    {
        $this->arguments[] = $argument;
        return $this;
    }

    public function validate(array $data): Result
    {
        $this->validationResults = new Result();
        foreach ($this->getArguments() as $argument) {
            $argumentName = $argument->getName();
            $argument->setValue(isset($data[$argumentName]) ? $data[$argumentName] : null);
            $propertyName = $argument->getName();
            if (!$argument->isValid()) {
                $this->validationResults->forProperty($propertyName)->merge(
                    HookHandler::trigger(
                        HookHandler::OUTLET_INPUT_INVALID,
                        [
                            'property' => $propertyName,
                            'argument' => $argument,
                            'validationResults' => $argument->getValidationResults(),
                        ]
                    )['validationResults']
                );
            }
        }

        return $this->validationResults;
    }

    public function isValid(): bool
    {
        if ($this->validationResults === null) {
            return true;
        }

        return !$this->validationResults->hasErrors();
    }

    public function getValidationResults(): Result
    {
        return $this->validationResults;
    }
}
