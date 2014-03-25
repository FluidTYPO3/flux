<?php
namespace FluidTYPO3\Flux\Form\Field;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class File extends AbstractMultiValueFormField {

	/**
	 * @var string
	 */
	protected $disallowed = '';

	/**
	 * @var string
	 */
	protected $allowed = '';

	/**
	 * @var integer
	 */
	protected $maxSize;

	/**
	 * @var string
	 */
	protected $uploadFolder;

	/**
	 * @var boolean
	 */
	protected $showThumbnails = FALSE;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('group');
		$configuration['disallowed'] = $this->getDisallowed();
		$configuration['allowed'] = $this->getAllowed();
		$configuration['max_size'] = $this->getMaxSize();
		$configuration['internal_type'] = 'file';
		$configuration['uploadfolder'] = $this->getUploadFolder();
		$configuration['show_thumbs'] = $this->getShowThumbnails();
		return $configuration;
	}

	/**
	 * Overrides parent method to ensure properly formatted
	 * default values for files
	 *
	 * @param mixed $default
	 * @return \FluidTYPO3\Flux\Form\FieldInterface
	 */
	public function setDefault($default) {
		if (NULL !== $default) {
			$files = array();
			$filePaths = GeneralUtility::trimExplode(',', $default);
			foreach ($filePaths as $path) {
				if (FALSE === strpos($path, '|')) {
					$files[] = $path . '|' . rawurlencode($path);
				} else {
					$files[] = $path;
				}
			}
			$default = implode(',', $files);
		}
		$this->default = $default;
		return $this;
	}

	/**
	 * @param string $allowed
	 * @return File
	 */
	public function setAllowed($allowed) {
		$this->allowed = $allowed;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAllowed() {
		return $this->allowed;
	}

	/**
	 * @param string $disallowed
	 * @return File
	 */
	public function setDisallowed($disallowed) {
		$this->disallowed = $disallowed;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDisallowed() {
		return $this->disallowed;
	}

	/**
	 * @param integer $maxSize
	 * @return File
	 */
	public function setMaxSize($maxSize) {
		$this->maxSize = $maxSize;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaxSize() {
		return $this->maxSize;
	}

	/**
	 * @param string $uploadFolder
	 * @return File
	 */
	public function setUploadFolder($uploadFolder) {
		$this->uploadFolder = $uploadFolder;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUploadFolder() {
		return $this->uploadFolder;
	}

	/**
	 * @param boolean $showThumbnails
	 * @return File
	 */
	public function setShowThumbnails($showThumbnails) {
		$this->showThumbnails = (boolean) $showThumbnails;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getShowThumbnails() {
		return (boolean) $this->showThumbnails;
	}

}
