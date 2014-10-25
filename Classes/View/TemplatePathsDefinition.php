<?php
namespace FluidTYPO3\Flux\View;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class TemplatePathsDefinition
 *
 * Defines a single set of paths for Fluid templates.
 */
class TemplatePathsDefinition {

	const PATTERN_TEMPLATEPATH = 'EXT:%s/Resources/Private/%s/';
	const PATTERN_TEMPLATES = 'Templates';
	const PATTERN_LAYOUTS = 'Layouts';
	const PATTERN_PARTIALS = 'Partials';

	/**
	 * @var string
	 */
	protected $extensionKey;

	/**
	 * @var TemplatePath
	 */
	protected $templateRootPath;

	/**
	 * @var TemplatePath
	 */
	protected $layoutRootPath;

	/**
	 * @var TemplatePath
	 */
	protected $partialRootPath;

	/**
	 * @var TemplatePath[]
	 */
	protected $templateRootPaths;

	/**
	 * @var TemplatePath[]
	 */
	protected $layoutRootPaths;

	/**
	 * @var TemplatePath[]
	 */
	protected $partialRootPaths;

	/**
	 * @param string $extensionKey
	 */
	public function __construct($extensionKey) {
		$this->extensionKey = $extensionKey;
		$this->setTemplateRootPaths(array($this->createDefaultTemplatePathObject(self::PATTERN_TEMPLATES)));
		$this->setLayoutRootPaths(array($this->createDefaultTemplatePathObject(self::PATTERN_LAYOUTS)));
		$this->setPartialRootPaths(array($this->createDefaultTemplatePathObject(self::PATTERN_PARTIALS)));
	}

	/**
	 * @param string $type
	 * @return TemplatePath
	 */
	protected function createDefaultTemplatePathObject($type) {
		$path = sprintf(self::PATTERN_TEMPLATEPATH, $this->extensionKey, $type);
		$path = GeneralUtility::getFileAbsFileName($path);
		$pathObject = new TemplatePath($path);
		return $pathObject;
	}

	/**
	 * @return TemplatePath
	 */
	public function getLayoutRootPath() {
		return $this->layoutRootPath;
	}

	/**
	 * @param TemplatePath $layoutRootPath
	 * @return void
	 */
	public function setLayoutRootPath(TemplatePath $layoutRootPath) {
		$this->layoutRootPath = $layoutRootPath;
	}

	/**
	 * @return TemplatePath
	 */
	public function getPartialRootPath() {
		return $this->partialRootPath;
	}

	/**
	 * @param TemplatePath $partialRootPath
	 * @return void
	 */
	public function setPartialRootPath(TemplatePath $partialRootPath) {
		$this->partialRootPath = $partialRootPath;
	}

	/**
	 * @return TemplatePath
	 */
	public function getTemplateRootPath() {
		return $this->templateRootPath;
	}

	/**
	 * @param TemplatePath $templateRootPath
	 * @return void
	 */
	public function setTemplateRootPath(TemplatePath $templateRootPath) {
		$this->templateRootPath = $templateRootPath;
	}

	/**
	 * @return TemplatePath[]
	 */
	public function getTemplateRootPaths() {
		return $this->templateRootPaths;
	}

	/**
	 * @param TemplatePath[] $templateRootPaths
	 * @return void
	 */
	public function setTemplateRootPaths(array $templateRootPaths) {
		$this->templateRootPaths = $templateRootPaths;
	}

	/**
	 * @return TemplatePath[]
	 */
	public function getPartialRootPaths() {
		return $this->partialRootPaths;
	}

	/**
	 * @param TemplatePath[] $partialRootPaths
	 * @return void
	 */
	public function setPartialRootPaths(array $partialRootPaths) {
		$this->partialRootPaths = $partialRootPaths;
	}

	/**
	 * @return TemplatePath[]
	 */
	public function getLayoutRootPaths() {
		return $this->layoutRootPaths;
	}

	/**
	 * @param TemplatePath[] $layoutRootPaths
	 * @return void
	 */
	public function setLayoutRootPaths(array $layoutRootPaths) {
		$this->layoutRootPaths = $layoutRootPaths;
	}

}
