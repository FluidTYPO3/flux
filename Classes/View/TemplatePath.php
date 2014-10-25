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

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class representing a single template path.
 */
class TemplatePath {

	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path = GeneralUtility::getFileAbsFileName($path);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->path;
	}

	/**
	 * @return boolean
	 */
	public function exists() {
		return file_exists($this->path);
	}

}
