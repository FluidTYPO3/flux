<?php
namespace FluidTYPO3\Flux\Package;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\View\TemplatePaths;

/**
 * Interface FluxPackageInterface
 *
 * Implemented by FluxPackage instances - describes required
 * methods for a package class (manifest API) that's unique
 * per extension.
 */
interface FluxPackageInterface
{

    /**
     * Constructor - takes an array of manifest data in the
     * same structure as in the manifest JSON file, or an
     * extension key in which case the expected manifest
     * is resolved using that and naming convention of the
     * manifest file. Or takes a full path to the manifest
     * file in which case the manifest is read from there.
     *
     * @param mixed $seedArrayOrExtensionKeyOrManifestPath
     */
    public function __construct($seedArrayOrExtensionKeyOrManifestPath);

    /**
     * Must always return the vendor name (as used in class
     * namespaces) corresponding to this package.
     *
     * @return string
     */
    public function getVendorName();

    /**
     * Must always return the ExtensionName format of the
     * extension key, excluding the vendor name.
     *
     * @return string
     */
    public function getExtensionName();

    /**
     * Must always return the $vendor\$extensioName format
     * of the vendor and extension name, using values from
     * the two preceeding methods.
     *
     * @return string
     */
    public function getNamespacePrefix();

    /**
     * Returns an instance of Flux's TemplatePaths class
     * with template paths of this package preloaded.
     *
     * @return TemplatePaths
     */
    public function getViewPaths();

    /**
     * Asserts whether or not this FluxPackage contains an
     * integration designed for the controller name, e.g.
     * "Content", "Page" etc.
     *
     * @param string $controllerName
     * @return boolean
     */
    public function isProviderFor($controllerName);

    /**
     * Asserts whether or not the feature is enabled for
     * this FluxPackage.
     *
     * @param string $featureName
     * @return boolean
     */
    public function isFeatureEnabled($featureName);

    /**
     * Get FQN of the class that's designated as an
     * implementation, meaning it is replaceable by Flux
     * Packages via this method.
     *
     * @param string $implementationName
     * @return string
     */
    public function getImplementation($implementationName);

    /**
     * Modify properties of this FluxPackage (in this class
     * instance only) by passing an array using the same
     * structure as the constructor and as in the manifest.
     *
     * Is used internally when applying compatibility values:
     * the "overlay" type subset of version dependent values
     * is detected based on current version and passed to
     * this function which then assimilates the values.
     *
     * @param array $alternativeManifestDeclaration
     * @return void
     */
    public function modify(array $alternativeManifestDeclaration);

    /**
     * Upcasts (promotes) the instance to another class name
     * by creating a new instance of the provided class and
     * passing $this->manifest as seed. If NULL is passed as
     * desired class name the expected final class name is
     * determined based on naming convention.
     *
     * @param string|NULL $desiredClassName
     * @return FluxPackageInterface
     */
    public function upcast($desiredClassName = null);
}
