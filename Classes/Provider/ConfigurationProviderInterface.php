<?php

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * ************************************************************* */

/**
 * @package Flux
 * @subpackage Provider
 */
interface Tx_Flux_Provider_ConfigurationProviderInterface {

	/**
	 * Return the extension key this processor belongs to
	 *
	 * @param array $row The record which triggered the processing
	 * @return string
	 */
	public function getExtensionKey(array $row);

	/**
	 * Get the absolute path to the template file containing the FlexForm
	 * field and sheets configuration. EXT:myext... syntax allowed
	 *
	 * @param array $row The record which triggered the processing
	 * @return string
	 */
	public function getTemplatePathAndFilename(array $row);

	/**
	 * Get an array of variables that should be used when rendering the
	 * FlexForm configuration
	 *
	 * @param array $row The record which triggered the processing
	 * @return array|NULL
	 */
	public function getTemplateVariables(array $row);

	/**
	 * Get paths for rendering the template, usual format i.e. partialRootPath,
	 * layoutRootPath, templateRootPath members must be in the returned array
	 *
	 * @param array $row
	 * @return array|NULL
	 */
	public function getTemplatePaths(array $row);

	/**
	 * Get the section name containing the FlexForm configuration. Return NULL
	 * if no sections are used. If you use sections in your template, you MUST
	 * use a section to contain the FlexForm configuration
	 *
	 * @param array $row The record which triggered the processing
	 * @return string|NULL
	 */
	public function getConfigurationSectionName(array $row);

	/**
	 * Get the field name which will trigger processing
	 *
	 * @param array $row The record which triggered the processing
	 * @return string|NULL
	 */
	public function getFieldName(array $row);


	/**
	 * Get the list_type value that will trigger processing
	 *
	 * @param array $row The record which triggered the processing
	 * @return string|NULL
	 */
	public function getTableName(array $row);

}

?>