<?php
/*****************************************************************
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
 *****************************************************************/

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

	/**
	 * Post-process the TCEforms DataStructure for a record associated
	 * with this ConfigurationProvider
	 *
	 * @param array $row
	 * @param mixed $dataStructure Array or string; should only be processed if argument is an array
	 * @param array $conf
	 * @return void
	 */
	public function postProcessDataStructure(array &$row, &$dataStructure, array $conf);

	/**
	 * Pre-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param array $row The record data, by reference. Changing fields' values changes the record's values before display
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently displaying the record
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, t3lib_TCEmain $reference);

	/**
	 * @abstract
	 * @param array $row The record data, by reference. Changing fields' values changes the record's values before display
	 * @return integer
	 */
	public function getPriority(array $row);

	/**
	 * Post-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row the record data, by reference. Changing fields' values changes the record's values just before saving
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently saving the record
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, t3lib_TCEmain $reference);

	/**
	 * Post-process database operation for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $status TYPO3 operation identifier, i.e. "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row The record's data, by reference. Changing fields' values changes the record's values just before saving after operation
	 * @param t3lib_TCEmain $reference A reference to the t3lib_TCEmain object that is currently performing the database operation
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, t3lib_TCEmain $reference);

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference);

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param t3lib_TCEmain $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, t3lib_TCEmain $reference);

	/**
	 * Perform operations upon clearing cache(s)
	 *
	 * @return void
	 */
	public function clearCacheCommand();

}
