<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * Group (select supertype) FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_GroupViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_SelectViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('items', 'mixed', 'Optional for this ViewHelper type', FALSE, FALSE);
		$this->registerArgument('internalType', 'string', 'FlexForm-internalType of this Group Selector', FALSE, 'db');
		$this->registerArgument('allowed', 'string', 'FlexForm-style "allowed" content for a group type field');
		$this->registerArgument('uploadFolder', 'string', 'Upload folder. DEPRECATED, will be moved to the File field ViewHelper');
	}

	/**
	 * Render method
	 * @return array
	 */
	public function renderConfiguration() {
		$config = $this->getFieldConfig();
		$config['type'] = 'group';
		$config['internal_type'] = $this->arguments['internalType'];
		$config['allowed'] = $this->arguments['allowed'];
		$config['uploadfolder'] = $this->arguments['uploadFolder'];
		return $config;
	}

}
