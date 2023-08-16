<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * Interface ControllerProviderInterface
 *
 * Contract for Providers which interact with controllers
 * to return controller name, action name etc.
 */
interface ControllerProviderInterface
{
    public function setPluginName(?string $pluginName): self;
    public function getPluginName(): ?string;
    public function setControllerName(string $controllerName): self;
    public function setControllerAction(string $controllerAction): self;
    public function getControllerExtensionKeyFromRecord(array $row, ?string $forField = null): string;
    public function getControllerActionFromRecord(array $row, ?string $forField = null): string;
    public function getControllerActionReferenceFromRecord(array $row, ?string $forField = null): string;

    /**
     * Implement to return a controller action name associated with $row.
     * Default strategy: return base name of Provider class minus the "Provider" suffix.
     */
    public function getControllerNameFromRecord(array $row): string;

    /**
     * Implement this and return a fully qualified VendorName.PackageName
     * value based on $row.
     */
    public function getControllerPackageNameFromRecord(array $row): string;
}
