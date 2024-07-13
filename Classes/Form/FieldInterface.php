<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface FieldInterface extends FormInterface
{
    public function buildConfiguration(): array;
    public function setClearable(bool $clearable): self;
    public function getClearable(): bool;
    public function getProtectable(): bool;
    public function setProtectable(bool $protectable): self;
    public function setNative(bool $native): self;
    public function isNative(): bool;
    public function setRequired(bool $required): self;
    public function getRequired(): bool;
    public function getPosition(): ?string;
    public function setPosition(?string $position): self;

    /**
     * @param string|array|null $displayCondition
     */
    public function setDisplayCondition($displayCondition): self;

    /**
     * @return string|array|null
     */
    public function getDisplayCondition();

    public function setRequestUpdate(bool $requestUpdate): self;
    public function getRequestUpdate(): bool;
    public function setExclude(bool $exclude): self;
    public function getExclude(): bool;
    public function setValidate(?string $validate): self;
    public function getValidate(): ?string;
    public function getConfig(): array;
    public function setConfig(array $config): self;

    /**
     * @param mixed $default
     */
    public function setDefault($default): self;

    /**
     * @return mixed
     */
    public function getDefault();
}
