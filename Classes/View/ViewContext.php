<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;

/**
 * Class ViewContext
 */
class ViewContext {

	/**
	 * @var string
	 */
	protected $packageName;

	/**
	 * @var string
	 */
	protected $controllerName;

	/**
	 * @var string
	 */
	protected $sectionName;

	/**
	 * @var array
	 */
	protected $variables = array();

	/**
	 * @var TemplatePaths
	 */
	protected $templatePaths;

	/**
	 * @var string
	 */
	protected $templatePathAndFilename;

	/**
	 * @var string
	 */
	protected $format = TemplatePaths::DEFAULT_FORMAT;

	/**
	 * @param string $templatePathAndFilename
	 * @param string $packageName
	 * @param string $controllerName
	 */
	public function __construct($templatePathAndFilename = NULL, $packageName = NULL, $controllerName = NULL) {
		$this->setTemplatePathAndFilename($templatePathAndFilename);
		$this->setPackageName($packageName);
		$this->setControllerName($controllerName);
	}

	/**
	 * @return string
	 */
	public function getExtensionKey() {
		return ExtensionNamingUtility::getExtensionKey($this->packageName);
	}

	/**
	 * @return string
	 */
	public function getExtensionName() {
		return ExtensionNamingUtility::getExtensionName($this->packageName);
	}

	/**
	 * @return string
	 */
	public function getVendorName() {
		return ExtensionNamingUtility::getVendorName($this->packageName);
	}

	/**
	 * @return string
	 */
	public function getPackageName() {
		return $this->packageName;
	}

	/**
	 * @param string $packageName
	 * @return void
	 */
	public function setPackageName($packageName) {
		$this->packageName = $packageName;
	}

	/**
	 * @return string
	 */
	public function getControllerName() {
		return $this->controllerName;
	}

	/**
	 * @param string $controllerName
	 * @return void
	 */
	public function setControllerName($controllerName) {
		$this->controllerName = $controllerName;
	}

	/**
	 * @return string
	 */
	public function getSectionName() {
		return $this->sectionName;
	}

	/**
	 * @param string $sectionName
	 * @return void
	 */
	public function setSectionName($sectionName) {
		$this->sectionName = $sectionName;
	}

	/**
	 * @return array
	 */
	public function getVariables() {
		return $this->variables;
	}

	/**
	 * @param array $variables
	 * @return void
	 */
	public function setVariables(array $variables) {
		$this->variables = $variables;
	}

	/**
	 * @return TemplatePaths
	 */
	public function getTemplatePaths() {
		if (TRUE === $this->templatePaths instanceof TemplatePaths) {
			return $this->templatePaths;
		}
		return new TemplatePaths($this->packageName);
	}

	/**
	 * @param TemplatePaths $templatePaths
	 */
	public function setTemplatePaths(TemplatePaths $templatePaths) {
		$this->templatePaths = $templatePaths;
	}

	/**
	 * @return string
	 */
	public function getFormat() {
		return $this->format;
	}

	/**
	 * @param string $format
	 * @return void
	 */
	public function setFormat($format) {
		$this->format = $format;
	}

	/**
	 * @return string
	 */
	public function getTemplatePathAndFilename() {
		return $this->templatePathAndFilename;
	}

	/**
	 * @param string $templatePathAndFilename
	 * @return void
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename) {
		$this->templatePathAndFilename = $templatePathAndFilename;
	}

}
