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

class UserFunction extends AbstractFormField implements FieldInterface
{
    protected array $arguments = [];
    protected string $function = '';
    protected string $renderType = '';

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('user');
        $configuration['userFunc'] = $this->getFunction();
        $configuration['renderType'] = $this->getRenderType();
        $configuration['arguments'] = $this->getArguments();
        return $configuration;
    }

    public function setFunction(string $function): self
    {
        $this->function = $function;
        return $this;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getRenderType(): string
    {
        return $this->renderType;
    }

    public function setRenderType(string $renderType): self
    {
        $this->renderType = $renderType;
        return $this;
    }
}
