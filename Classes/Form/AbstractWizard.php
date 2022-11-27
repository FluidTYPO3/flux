<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * AbstractWizard
 */
abstract class AbstractWizard extends AbstractFormComponent implements WizardInterface
{
    protected bool $hideParent = false;
    protected ?string $name = null;
    protected ?string $type = null;
    protected ?string $icon = null;
    protected array $module = [];

    public function build(): array
    {
        $structure = [
            'type' => $this->type,
            'title' => $this->getLabel(),
            'icon' => $this->icon,
            'hideParent' => intval($this->getHideParent()),
            'module' => $this->module
        ];
        $configuration = $this->buildConfiguration();
        $structure = array_merge($structure, $configuration);
        return $structure;
    }

    public function setHideParent(bool $hideParent): self
    {
        $this->hideParent = $hideParent;
        return $this;
    }

    public function getHideParent(): bool
    {
        return $this->hideParent;
    }

    public function hasChildren(): bool
    {
        return false;
    }
}
