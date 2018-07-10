<?php
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
    /**
     * @param string $controllerName
     * @return $this
     */
    public function setControllerName($controllerName);

    /**
     * @param string $controllerAction
     * @return $this
     */
    public function setControllerAction($controllerAction);

    /**
     * Implement to return a controller action name associated with $row.
     * Default strategy: return base name of Provider class minus the "Provider" suffix.
     *
     * @param array $row
     * @return string
     */
    public function getControllerNameFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerExtensionKeyFromRecord(array $row);

    /**
     * Implement this and return a fully qualified VendorName.PackageName
     * value based on $row.
     *
     * @param array $row
     * @return string
     */
    public function getControllerPackageNameFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerActionFromRecord(array $row);

    /**
     * @param array $row
     * @return string
     */
    public function getControllerActionReferenceFromRecord(array $row);
}
