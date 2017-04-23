<?php
namespace FluidTYPO3\Flux;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Package\FluxPackageInterface;
use FluidTYPO3\Flux\Package\PackageNotFoundException;
use FluidTYPO3\Flux\Utility\CompatibilityRegistry;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use FluidTYPO3\Flux\View\TemplatePaths;
use FluidTYPO3\Flux\View\ViewContext;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * Class FluxPackage
 */
class FluxPackage implements FluxPackageInterface
{

    const IMPLEMENTATION_VIEW = 'view';
    const IMPLEMENTATION_VIEWCONTEXT = 'viewContext';
    const IMPLEMENTATION_RENDERINGCONTEXT = 'renderingContext';
    const IMPLEMENTATION_FORM = 'form';
    const IMPLEMENTATION_TEMPLATEPATHS = 'templatePaths';
    const IMPLEMENTATION_TEMPLATEVARIABLEPROVIDER = 'templateVariableProvider';
    const IMPLEMENTATION_VIEWHELPERRESOLVER = 'viewHelperResolver';
    const IMPLEMENTATION_VIEWHELPERINVOKER = 'viewHelperInvoker';

    const FEATURE_PREVIEW = 'preview';
    const FEATURE_TRANSFORMATION = 'transformation';
    const FEATURE_DATAHANDLING = 'dataHandling';

    /**
     * @var array
     */
    protected $manifest = [];

    /**
     * @var array
     */
    protected $defaultImplementations = [
        self::IMPLEMENTATION_VIEW => ExposedTemplateView::class,
        self::IMPLEMENTATION_VIEWCONTEXT => ViewContext::class,
        self::IMPLEMENTATION_RENDERINGCONTEXT => RenderingContext::class,
        self::IMPLEMENTATION_FORM => Form::class,
        self::IMPLEMENTATION_TEMPLATEPATHS => TemplatePaths::class,
        self::IMPLEMENTATION_TEMPLATEVARIABLEPROVIDER => TemplateVariableContainer::class,
        self::IMPLEMENTATION_VIEWHELPERRESOLVER => null, // @TODO: fill when standalone fluid arrives
        self::IMPLEMENTATION_VIEWHELPERINVOKER => null // @TODO: fill when standalone fluid arrives
    ];

    /**
     * @param mixed $seedArrayOrExtensionKeyOrManifestPath
     * @return FluxPackageInterface
     */
    public static function create($seedArrayOrExtensionKeyOrManifestPath)
    {
        return new static($seedArrayOrExtensionKeyOrManifestPath);
    }

    /**
     * Constructor - takes an array of manifest data in the
     * same structure as in the manifest JSON file, or an
     * extension key in which case the expected manifest
     * is resolved using that and naming convention of the
     * manifest file. Or takes a full path to the manifest
     * file in which case the manifest is read from there.
     *
     * Note: applies CompatibilityRegistry-resolved versioned
     * manifest configuration values immediately.
     *
     * @param mixed $seedArrayOrExtensionKeyOrManifestPath
     */
    public function __construct($seedArrayOrExtensionKeyOrManifestPath)
    {
        if (is_array($seedArrayOrExtensionKeyOrManifestPath)) {
            $this->manifest = $seedArrayOrExtensionKeyOrManifestPath;
        } else {
            $possibleExtensionKey = ExtensionNamingUtility::getExtensionKey($seedArrayOrExtensionKeyOrManifestPath);
            if (ExtensionManagementUtility::isLoaded($possibleExtensionKey)) {
                $this->manifest = $this->loadManifestFile(
                    ExtensionManagementUtility::extPath($possibleExtensionKey, 'flux.json')
                );
            } else {
                $this->manifest = $this->loadManifestFile($seedArrayOrExtensionKeyOrManifestPath);
            }
        }
        if (!empty($this->manifest['compatibility'])) {
            $scope = $this->manifest['package'] . '/ManifestOverlay';
            CompatibilityRegistry::register($scope, $this->manifest['compatibility']);
            RecursiveArrayUtility::mergeRecursiveOverrule($this->manifest, CompatibilityRegistry::get($scope));
        }
    }

    /**
     * Load flux.json manifest file by path or reference.
     *
     * @param string $filePathAndFilename
     * @return array
     */
    protected function loadManifestFile($filePathAndFilename)
    {
        if (strpos($filePathAndFilename, '/') !== 0) {
            $absoluteManifestPath = GeneralUtility::getFileAbsFileName($filePathAndFilename);
        } else {
            $absoluteManifestPath = $filePathAndFilename;
        }
        if (!file_exists($absoluteManifestPath)) {
            throw new PackageNotFoundException(
                sprintf(
                    'Flux manifest file not found! I looked for %s - make sure the manifest exists.',
                    $absoluteManifestPath
                )
            );
        }
        return json_decode(file_get_contents($absoluteManifestPath), JSON_OBJECT_AS_ARRAY);
    }

    /**
     * Must always return the vendor name (as used in class
     * namespaces) corresponding to this package.
     *
     * @return string
     */
    public function getVendorName()
    {
        return ExtensionNamingUtility::getVendorName($this->manifest['package']);
    }

    /**
     * Must always return the ExtensionName format of the
     * extension key, excluding the vendor name.
     *
     * @return string
     */
    public function getExtensionName()
    {
        return ExtensionNamingUtility::getExtensionName($this->manifest['package']);
    }

    /**
     * Must always return the $vendor\$extensioName format
     * of the vendor and extension name, using values from
     * the two preceeding methods.
     *
     * @return string
     */
    public function getNamespacePrefix()
    {
        return $this->getVendorName() . '\\' . $this->getExtensionName() . '\\';
    }

    /**
     * Returns an instance of Flux's TemplatePaths class
     * with template paths of this package preloaded.
     *
     * @return TemplatePaths
     */
    public function getViewPaths()
    {
        return new TemplatePaths(!empty($this->manifest['view']) ? $this->manifest['view'] : $this->getExtensionName());
    }

    /**
     * Asserts whether or not this FluxPackage contains an
     * integration designed for the controller name, e.g.
     * "Content", "Page" etc.
     *
     * @param string $controllerName
     * @return boolean
     */
    public function isProviderFor($controllerName)
    {
        return !empty($this->manifest['providers']) && in_array($controllerName, $this->manifest['providers']);
    }

    /**
     * Asserts whether or not the feature is enabled for
     * this FluxPackage.
     *
     * @param string $featureName
     * @return boolean
     */
    public function isFeatureEnabled($featureName)
    {
        return !empty($this->manifest['features']) && in_array($featureName, $this->manifest['features']);
    }

    /**
     * Get FQN of the class that's designated as an
     * implementation, meaning it is replaceable by Flux
     * Packages via this method.
     *
     * @param string $name
     * @return string
     */
    public function getImplementation($name)
    {
        if (!empty($this->manifest['implementations']) && array_key_exists($name, $this->manifest['implementations'])) {
            return $this->manifest['implementations'][$name];
        }
        return $this->defaultImplementations[$name];
    }

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
    public function modify(array $alternativeManifestDeclaration)
    {
        RecursiveArrayUtility::mergeRecursiveOverrule($this->manifest, $alternativeManifestDeclaration);
    }

    /**
     * Upcasts (promotes) the instance to another class name
     * by creating a new instance of the provided class and
     * passing $this->manifest as seed. If NULL is passed as
     * desired class name the expected final class name is
     * determined based on naming convention.
     *
     * @param string $desiredClassName
     * @return FluxPackageInterface
     */
    public function upcast($desiredClassName = null)
    {
        if (!$desiredClassName) {
            $desiredClassName = $this->getNamespacePrefix() . 'FluxPackage';
        }
        return new $desiredClassName($this->manifest);
    }
}
