<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * FieldInterface
 *
 * @deprecated Will be removed in Flux 10.0
 */
interface FieldInterface extends FormInterface
{
    public function buildConfiguration(): array;
    public function setClearable(bool $clearable): self;
    public function getClearable(): bool;
    public function setRequired(bool $required): self;
    public function getRequired(): bool;
    public function setDisplayCondition(string $displayCondition): self;
    public function getDisplayCondition(): ?string;
    public function setRequestUpdate(bool $requestUpdate): self;
    public function getRequestUpdate(): bool;
    public function setExclude(bool $exclude): self;
    public function getExclude(): bool;
    public function setValidate(?string $validate): self;
    public function getValidate(): ?string;
    public function getConfig(): array;
    public function setConfig(array $config): self;
    public function add(WizardInterface $wizard): self;
    public function remove(string $wizardName): ?WizardInterface;

    /**
     * @param mixed $default
     */
    public function setDefault($default): self;

    /**
     * @return mixed
     */
    public function getDefault();
}
