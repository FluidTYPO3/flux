<?php
namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\Interfaces\ControllerProviderInterface;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;

class MisconfiguredControllerProvider implements ControllerProviderInterface, FormProviderInterface
{
    public function setPluginName(?string $pluginName): ControllerProviderInterface
    {
        return $this;
    }

    public function getPluginName(): ?string
    {
        return '';
    }

    public function setControllerName(string $controllerName): ControllerProviderInterface
    {
        return $this;
    }

    public function setControllerAction(string $controllerAction): ControllerProviderInterface
    {
        return $this;
    }

    public function getControllerExtensionKeyFromRecord(array $row): string
    {
        return '';
    }

    public function getControllerActionFromRecord(array $row): string
    {
        return '';
    }

    public function getControllerActionReferenceFromRecord(array $row): string
    {
        return '';
    }

    public function getControllerNameFromRecord(array $row): string
    {
        return '';
    }

    public function getControllerPackageNameFromRecord(array $row): string
    {
        return '';
    }

    public function getForm(array $row, ?string $forField = null): ?Form
    {
        return null;
    }

    public function setForm(Form $form): FormProviderInterface
    {
        return $this;
    }
}
