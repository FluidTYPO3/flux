<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
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
 */
class TemplatePaths {

	const DEFAULT_TEMPLATES_DIRECTORY = 'Resources/Private/Templates/';
	const DEFAULT_LAYOUTS_DIRECTORY = 'Resources/Private/Layouts/';
	const DEFAULT_PARTIALS_DIRECTORY = 'Resources/Private/Partials/';
	const DEFAULT_FORMAT = 'html';
	const CONFIG_TEMPLATEROOTPATH = 'templateRootPath';
	const CONFIG_TEMPLATEROOTPATHS = 'templateRootPaths';
	const CONFIG_LAYOUTROOTPATH = 'layoutRootPath';
	const CONFIG_LAYOUTROOTPATHS = 'layoutRootPaths';
	const CONFIG_PARTIALROOTPATH = 'partialRootPath';
	const CONFIG_PARTIALROOTPATHS = 'partialRootPaths';
	const CONFIG_OVERLAYS = 'overlays';

	/**
	 * @var array
	 */
	protected $templateRootPaths = array();

	/**
	 * @var array
	 */
	protected $layoutRootPaths = array();

	/**
	 * @var array
	 */
	protected $partialRootPaths = array();

	/**
	 * @param string|NULL $packageNameOrTypoScriptArray
	 */
	public function __construct($packageNameOrTypoScriptArray = NULL) {
		if (TRUE === is_array($packageNameOrTypoScriptArray)) {
			$this->fillFromTypoScriptArray($packageNameOrTypoScriptArray);
		} elseif (FALSE === empty($packageNameOrTypoScriptArray)) {
			$this->fillDefaultsByPackageName($packageNameOrTypoScriptArray);
		}
	}

	/**
	 * @return array
	 */
	public function getTemplateRootPaths() {
		return $this->templateRootPaths;
	}

	/**
	 * @param array $templateRootPaths
	 * @return void
	 */
	public function setTemplateRootPaths(array $templateRootPaths) {
		$this->templateRootPaths = $templateRootPaths;
	}

	/**
	 * @return array
	 */
	public function getLayoutRootPaths() {
		return $this->layoutRootPaths;
	}

	/**
	 * @param array $layoutRootPaths
	 * @return void
	 */
	public function setLayoutRootPaths(array $layoutRootPaths) {
		$this->layoutRootPaths = $layoutRootPaths;
	}

	/**
	 * @return array
	 */
	public function getPartialRootPaths() {
		return $this->partialRootPaths;
	}

	/**
	 * @param array $partialRootPaths
	 * @return void
	 */
	public function setPartialRootPaths(array $partialRootPaths) {
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
	public function resolveTemplateFileForControllerAndActionAndFormat($controller, $action, $format = self::DEFAULT_FORMAT) {
		$action = ucfirst($action);
		foreach ($this->getTemplateRootPaths() as $templateRootPath) {
			$candidate = $templateRootPath . $controller . '/' . $action . '.' . $format;
			$candidate = $this->ensureAbsolutePath($candidate);
			if (TRUE === file_exists($candidate)) {
				return $candidate;
			}
		}
		return NULL;
	}

	/**
	 * @param string $controllerName
	 * @param string $format
	 * @return array
	 */
	public function resolveAvailableTemplateFiles($controllerName, $format = self::DEFAULT_FORMAT) {
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
	public function resolveAvailablePartialFiles($format = self::DEFAULT_FORMAT) {
		return $this->resolveFilesInFolders($this->getPartialRootPaths(), $format);
	}

	/**
	 * @param string $format
	 * @return array
	 */
	public function resolveAvailableLayoutFiles($format = self::DEFAULT_FORMAT) {
		return $this->resolveFilesInFolders($this->getLayoutRootPaths(), $format);
	}

	/**
	 * @param array $folders
	 * @param string $format
	 * @return array
	 */
	protected function resolveFilesInFolders(array $folders, $format) {
		$files = array();
		foreach ($folders as $folder) {
			$files = array_merge($files, GeneralUtility::getAllFilesAndFoldersInPath(array(), $folder, $format));
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
	public function fillFromTypoScriptArray(array $paths) {
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
	public function fillDefaultsByPackageName($packageName) {
		$extensionKey = ExtensionNamingUtility::getExtensionKey($packageName);
		$extensionPath = ExtensionManagementUtility::extPath($extensionKey);
		$this->setTemplateRootPaths(array($extensionPath . self::DEFAULT_TEMPLATES_DIRECTORY));
		$this->setLayoutRootPaths(array($extensionPath . self::DEFAULT_LAYOUTS_DIRECTORY));
		$this->setPartialRootPaths(array($extensionPath . self::DEFAULT_PARTIALS_DIRECTORY));
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array(
			self::CONFIG_TEMPLATEROOTPATHS => $this->getTemplateRootPaths(),
			self::CONFIG_LAYOUTROOTPATHS => $this->getLayoutRootPaths(),
			self::CONFIG_PARTIALROOTPATHS => $this->getPartialRootPaths()
		);
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
	protected function ensureAbsolutePath($reference) {
		if (FALSE === is_array($reference)) {
			$filename = ('/' !== $reference{0} ? GeneralUtility::getFileAbsFileName($reference) : $reference);
		} else {
			foreach ($reference as &$subValue) {
				$subValue = $this->ensureAbsolutePath($subValue);
			}
			return $reference;
		}
		return $filename;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	protected function ensureSuffixedPath($path) {
		return rtrim($path, '/') . '/';
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
	protected function extractPathArrays(array $paths) {
		$templateRootPaths = array();
		$layoutRootPaths = array();
		$partialRootPaths = array();
		// first recorded: the modern plural paths configurations:
		// checked as first candidates; sorted reverse by key to
		// check the path with the highest number first.
		if (TRUE === isset($paths[self::CONFIG_TEMPLATEROOTPATHS]) && TRUE === is_array($paths[self::CONFIG_TEMPLATEROOTPATHS])) {
			krsort($paths[self::CONFIG_TEMPLATEROOTPATHS], SORT_NUMERIC);
			$templateRootPaths = array_merge($templateRootPaths, array_values($paths[self::CONFIG_TEMPLATEROOTPATHS]));
		}
		if (TRUE === isset($paths[self::CONFIG_LAYOUTROOTPATHS]) && TRUE === is_array($paths[self::CONFIG_LAYOUTROOTPATHS])) {
			krsort($paths[self::CONFIG_LAYOUTROOTPATHS], SORT_NUMERIC);
			$layoutRootPaths = array_merge($layoutRootPaths, array_values($paths[self::CONFIG_LAYOUTROOTPATHS]));
		}
		if (TRUE === isset($paths[self::CONFIG_PARTIALROOTPATHS]) && TRUE === is_array($paths[self::CONFIG_PARTIALROOTPATHS])) {
			krsort($paths[self::CONFIG_PARTIALROOTPATHS], SORT_NUMERIC);
			$partialRootPaths = array_merge($partialRootPaths, array_values($paths[self::CONFIG_PARTIALROOTPATHS]));
		}
		// second recorded: the legacy "overlays." configurations
		// by recursive call to extraction method:
		if (TRUE === isset($paths[self::CONFIG_OVERLAYS])) {
			foreach ($paths[self::CONFIG_OVERLAYS] as $overlayGroup) {
				list ($overlayTemplates, $overlayLayouts, $overlayPartials) = $this->extractPathArrays($overlayGroup);
				$templateRootPaths = array_merge($templateRootPaths, $overlayTemplates);
				$layoutRootPaths = array_merge($layoutRootPaths, $overlayLayouts);
				$partialRootPaths = array_merge($partialRootPaths, $overlayPartials);
			}
		}
		// last appended if set: the legacy singular paths configuration
		if (TRUE === isset($paths[self::CONFIG_TEMPLATEROOTPATH])) {
			$templateRootPaths[] = $paths[self::CONFIG_TEMPLATEROOTPATH];
		}
		if (TRUE === isset($paths[self::CONFIG_LAYOUTROOTPATH])) {
			$layoutRootPaths[] = $paths[self::CONFIG_LAYOUTROOTPATH];
		}
		if (TRUE === isset($paths[self::CONFIG_PARTIALROOTPATH])) {
			$partialRootPaths[] = $paths[self::CONFIG_PARTIALROOTPATH];
		}
		// make sure every path is suffixed by a trailing slash:
		$templateRootPaths = array_map(array($this, 'ensureSuffixedPath'), $templateRootPaths);
		$layoutRootPaths = array_map(array($this, 'ensureSuffixedPath'), $layoutRootPaths);
		$partialRootPaths = array_map(array($this, 'ensureSuffixedPath'), $partialRootPaths);
		$templateRootPaths = array_unique($templateRootPaths);
		$partialRootPaths = array_unique($partialRootPaths);
		$layoutRootPaths = array_unique($layoutRootPaths);
		$templateRootPaths = array_values($templateRootPaths);
		$layoutRootPaths = array_values($layoutRootPaths);
		$partialRootPaths = array_values($partialRootPaths);
		$pathCollections = array($templateRootPaths, $layoutRootPaths, $partialRootPaths);
		$pathCollections = $this->ensureAbsolutePath($pathCollections);
		return $pathCollections;
	}

}
