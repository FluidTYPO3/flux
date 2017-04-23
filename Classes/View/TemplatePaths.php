<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Template Paths Holder
 *
 * Class used to hold and resolve template files
 * and paths in multiple supported ways.
 *
 * The purpose of this class is to homogenise the
 * API that is used when working with template
 * paths coming from TypoScript, as well as serve
 * as a way to quickly generate default template-,
 * layout- and partial root paths by package.
 *
 * The constructor accepts two different types of
 * input - anything not of those types is silently
 * ignored:
 *
 * - a `string` input is assumed a package name
 *   and will call the `fillDefaultsByPackageName`
 *   value filling method.
 * - an `array` input is assumed a TypoScript-style
 *   array of root paths in one or more of the
 *   supported structures and will call the
 *   `fillFromTypoScriptArray` method.
 *
 * Either method can also be called after instance
 * is created, but both will overwrite any paths
 * you have previously configured.
 *
 * @deprecated To be removed in next major release
 */
class TemplatePaths
{

    const DEFAULT_TEMPLATES_DIRECTORY = 'Resources/Private/Templates/';
    const DEFAULT_LAYOUTS_DIRECTORY = 'Resources/Private/Layouts/';
    const DEFAULT_PARTIALS_DIRECTORY = 'Resources/Private/Partials/';
    const DEFAULT_FORMAT = 'html';
    const CONFIG_TEMPLATEROOTPATHS = 'templateRootPaths';
    const CONFIG_LAYOUTROOTPATHS = 'layoutRootPaths';
    const CONFIG_PARTIALROOTPATHS = 'partialRootPaths';

    /**
     * @var array
     */
    protected $templateRootPaths = [];

    /**
     * @var array
     */
    protected $layoutRootPaths = [];

    /**
     * @var array
     */
    protected $partialRootPaths = [];

    /**
     * @var boolean|null
     */
    protected static $needReverseOrder;

    /**
     * @param string|NULL $packageOrPaths
     */
    public function __construct($packageOrPaths = null)
    {
        if (true === is_array($packageOrPaths)) {
            $this->fillFromTypoScriptArray($packageOrPaths);
        } elseif (false === empty($packageOrPaths)
            && ExtensionManagementUtility::isLoaded(ExtensionNamingUtility::getExtensionKey($packageOrPaths))
        ) {
            $this->fillDefaultsByPackageName($packageOrPaths);
        }
        // initiliaze the flag for sorting templatePaths
        if (static::$needReverseOrder === null) {
            static::$needReverseOrder = version_compare(TYPO3_version, '8.0', '<');
        }

    }

    /**
     * @return array
     */
    public function getTemplateRootPaths()
    {
        return $this->templateRootPaths;
    }

    /**
     * @param array $templateRootPaths
     * @return void
     */
    public function setTemplateRootPaths(array $templateRootPaths)
    {
        $this->templateRootPaths = $templateRootPaths;
    }

    /**
     * @return array
     */
    public function getLayoutRootPaths()
    {
        return $this->layoutRootPaths;
    }

    /**
     * @param array $layoutRootPaths
     * @return void
     */
    public function setLayoutRootPaths(array $layoutRootPaths)
    {
        $this->layoutRootPaths = $layoutRootPaths;
    }

    /**
     * @return array
     */
    public function getPartialRootPaths()
    {
        return $this->partialRootPaths;
    }

    /**
     * @param array $partialRootPaths
     * @return void
     */
    public function setPartialRootPaths(array $partialRootPaths)
    {
        $this->partialRootPaths = $partialRootPaths;
    }

    /**
     * Attempts to resolve an absolute filename
     * of a template (i.e. `templateRootPaths`)
     * using a controller name, action and format.
     *
     * Works _backwards_ through template paths in
     * order to achieve an "overlay"-type behavior
     * where the last paths added are the first to
     * be checked and the first path added acts as
     * fallback if no other paths have the file.
     *
     * If the file does not exist in any path,
     * including fallback path, `NULL` is returned.
     *
     * Path configurations filled from TypoScript
     * is automatically recorded in the right
     * order (see `fillFromTypoScriptArray`), but
     * when manually setting the paths that should
     * be checked, you as user must be aware of
     * this reverse behavior (which you should
     * already be, given that it is the same way
     * TypoScript path configurations work).
     *
     * @param string $controller
     * @param string $action
     * @param string $format
     * @return string|NULL
     * @api
     */
    public function resolveTemplateFileForControllerAndActionAndFormat(
        $controller,
        $action,
        $format = self::DEFAULT_FORMAT
    ) {
        $action = ucfirst($action);
        foreach ($this->getTemplateRootPaths() as $templateRootPath) {
            $candidate = $templateRootPath . $controller . '/' . $action . '.' . $format;
            $candidate = $this->ensureAbsolutePath($candidate);
            if (true === file_exists($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * @param string $controllerName
     * @param string $format
     * @return array
     */
    public function resolveAvailableTemplateFiles($controllerName, $format = self::DEFAULT_FORMAT)
    {
        $paths = $this->getTemplateRootPaths();
        foreach ($paths as $index => $path) {
            $paths[$index] = $path . $controllerName . '/';
        }
        return $this->resolveFilesInFolders($paths, $format);
    }

    /**
     * @param string $format
     * @return array
     */
    public function resolveAvailablePartialFiles($format = self::DEFAULT_FORMAT)
    {
        return $this->resolveFilesInFolders($this->getPartialRootPaths(), $format);
    }

    /**
     * @param string $format
     * @return array
     */
    public function resolveAvailableLayoutFiles($format = self::DEFAULT_FORMAT)
    {
        return $this->resolveFilesInFolders($this->getLayoutRootPaths(), $format);
    }

    /**
     * @param array $folders
     * @param string $format
     * @return array
     */
    protected function resolveFilesInFolders(array $folders, $format)
    {
        $files = [];
        foreach ($folders as $folder) {
            $files = array_merge($files, GeneralUtility::getAllFilesAndFoldersInPath([], $folder, $format));
        }
        return array_values($files);
    }

    /**
     * Fills path arrays based on a traditional
     * TypoScript array which may contain one or
     * more of the supported structures, in order
     * of priority:
     *
     * - `plugin.tx_yourext.view.templateRootPath` and siblings.
     * - `plugin.tx_yourext.view.templateRootPaths` and siblings.
     * - `plugin.tx_yourext.view.overlays.otherextension.templateRootPath` and siblings.
     *
     * The paths are treated as follows, using the
     * `template`-type paths as an example:
     *
     * - If `templateRootPath` is defined, it gets
     *   used as the _first_ path in the internal
     *   paths array.
     * - If `templateRootPaths` is defined, all
     *   values from it are _appended_ to the
     *   internal paths array.
     * - If `overlays.*` exists in the array it is
     *   iterated, each `templateRootPath` entry
     *   from it _appended_ to the internal array.
     *
     * The result is that after filling, the path
     * arrays will contain one or more entries in
     * the order described above, depending on how
     * many of the possible configurations were
     * present in the input array.
     *
     * Will replace any currently configured paths.
     *
     * @param array $paths
     * @return void
     * @api
     */
    public function fillFromTypoScriptArray(array $paths)
    {
        list ($templateRootPaths, $layoutRootPaths, $partialRootPaths) = $this->extractPathArrays($paths);
        $this->setTemplateRootPaths($templateRootPaths);
        $this->setLayoutRootPaths($layoutRootPaths);
        $this->setPartialRootPaths($partialRootPaths);
    }

    /**
     * Fills path arrays with default expected paths
     * based on package name (converted to extension
     * key automatically).
     *
     * Will replace any currently configured paths.
     *
     * @param string $packageName
     * @return void
     * @api
     */
    public function fillDefaultsByPackageName($packageName)
    {
        $extensionKey = ExtensionNamingUtility::getExtensionKey($packageName);
        $extensionPath = ExtensionManagementUtility::extPath($extensionKey);
        $this->setTemplateRootPaths([$extensionPath . self::DEFAULT_TEMPLATES_DIRECTORY]);
        $this->setLayoutRootPaths([$extensionPath . self::DEFAULT_LAYOUTS_DIRECTORY]);
        $this->setPartialRootPaths([$extensionPath . self::DEFAULT_PARTIALS_DIRECTORY]);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            self::CONFIG_TEMPLATEROOTPATHS => $this->getTemplateRootPaths(),
            self::CONFIG_LAYOUTROOTPATHS => $this->getLayoutRootPaths(),
            self::CONFIG_PARTIALROOTPATHS => $this->getPartialRootPaths()
        ];
    }

    /**
     * Guarantees that $reference is turned into a
     * correct, absolute path. The input can be a
     * relative path or a FILE: or EXT: reference
     * but cannot be a FAL resource identifier.
     *
     * @param mixed $reference
     * @return mixed
     */
    protected function ensureAbsolutePath($reference)
    {
        return PathUtility::translatePath($reference);
    }

    /**
     * Initalize rootPaths
     * TYPO3 < 8.0 we need the rootpaths sorted reverse by key to check the path with the highest number first.
     * TYPO3 >= 8.0 the highest priority paths must come last.
     *
     * @param mixed $paths
     * @return array
     */
    protected function initializeRootPaths($paths)  {
        $rootPaths = [];
        if (is_array($paths)) {
            // reverse order is needed for TYPO3 Verion < 8.0
            if (static::$needReverseOrder) {
                krsort($paths, SORT_NUMERIC);
            } else {
                ksort($paths, SORT_NUMERIC);
            }
            $rootPaths = array_merge($rootPaths, $paths);
        }
        return $rootPaths;
    }

    /**
     * Extract an array of three arrays of paths, one
     * for each of the types of Fluid file resources.
     * Accepts one or both of the singular and plural
     * path definitions in the input - returns the
     * combined collections of paths based on both
     * the singular and plural entries with the singular
     * entries being recorded first and plurals second.
     *
     * Sorts the passed paths by index in array, in
     * reverse, so that the base View class will iterate
     * the array in the right order when resolving files.
     *
     * Adds legacy singular name as last option, if set.
     *
     * @param array $paths
     * @return array
     */
    protected function extractPathArrays(array $paths)
    {
        // The modern plural paths configurations:
        $templateRootPaths = $this->initializeRootPaths($paths[self::CONFIG_TEMPLATEROOTPATHS]);
        $layoutRootPaths = $this->initializeRootPaths($paths[self::CONFIG_LAYOUTROOTPATHS]);
        $partialRootPaths = $this->initializeRootPaths($paths[self::CONFIG_PARTIALROOTPATHS]);
        // translate all paths to absolute paths
        $templateRootPaths = array_map([$this, 'ensureAbsolutePath'], $templateRootPaths);
        $layoutRootPaths = array_map([$this, 'ensureAbsolutePath'], $layoutRootPaths);
        $partialRootPaths = array_map([$this, 'ensureAbsolutePath'], $partialRootPaths);
        $templateRootPaths = array_unique($templateRootPaths);
        $partialRootPaths = array_unique($partialRootPaths);
        $layoutRootPaths = array_unique($layoutRootPaths);
        $templateRootPaths = array_values($templateRootPaths);
        $layoutRootPaths = array_values($layoutRootPaths);
        $partialRootPaths = array_values($partialRootPaths);
        return [$templateRootPaths, $layoutRootPaths, $partialRootPaths];
    }
}
