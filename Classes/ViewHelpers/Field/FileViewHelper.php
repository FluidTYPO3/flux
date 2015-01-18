<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\File;

/**
 * Group (select supertype) FlexForm field ViewHelper, subtype "file"
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class FileViewHelper extends AbstractMultiValueFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('maxSize', 'integer', 'Maximum file size allowed in KB');
		$this->registerArgument('allowed', 'string', 'Defines a list of file types allowed in this field');
		$this->registerArgument('disallowed', 'string', 'Defines a list of file types NOT allowed in this field');
		$this->registerArgument('uploadFolder', 'string', 'Upload folder. DEPRECATED, will be moved to the File field ViewHelper');
		$this->registerArgument('showThumbnails', 'boolean', 'If TRUE, displays thumbnails for selected values', FALSE, FALSE);
	}

	/**
	 * @return File
	 */
	public function getComponent() {
		/** @var File $component */
		$component = $this->getPreparedComponent('File');
		$component->setMaxSize($this->arguments['maxSize']);
		$component->setDisallowed($this->arguments['disallowed']);
		$component->setAllowed($this->arguments['allowed']);
		$component->setUploadFolder($this->arguments['uploadFolder']);
		$component->setShowThumbnails($this->arguments['showThumbnails']);
		return $component;
	}

}
