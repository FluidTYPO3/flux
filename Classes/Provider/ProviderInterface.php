<?php
namespace FluidTYPO3\Flux\Provider;
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Grid;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * @package Flux
 * @subpackage Provider
 */
interface ProviderInterface {

	/**
	 * @param array $settings
	 * @return void
	 */
	public function loadSettings(array $settings);

	/**
	 * Must return TRUE if this ConfigurationProvider instance wants
	 * to be the one used for proccesing $row
	 *
	 * @param array $row
	 * @param string $table
	 * @param string $field
	 * @param string $extensionKey
	 * @return boolean
	 */
	public function trigger(array $row, $table, $field, $extensionKey = NULL);

	/**
	 * Returns an instance of \FluidTYPO3\Flux\Form as required by this record.
	 *
	 * @param array $row
	 * @return Form|NULL
	 */
	public function getForm(array $row);

	/**
	 * Returns a \FluidTYPO3\Flux\Form\Container\Grid as required by this record.
	 *
	 * @param array $row
	 * @return Grid
	 */
	public function getGrid(array $row);

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
	 * @return string|NULL
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
	 * @return array
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
	 * @param string $tableName
	 * @return void
	 */
	public function setTableName($tableName);

	/**
	 * @param string $fieldName
	 * @return void
	 */
	public function setFieldName($fieldName);

	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @param string $name
	 */
	public function setName($name);

	/**
	 * @param string $extensionKey
	 * @return void
	 */
	public function setExtensionKey($extensionKey);

	/**
	 * @param array|NULL $templateVariables
	 * @return void
	 */
	public function setTemplateVariables($templateVariables);

	/**
	 * @param string $templatePathAndFilename
	 * @return void
	 */
	public function setTemplatePathAndFilename($templatePathAndFilename);

	/**
	 * @param array|NULL $templatePaths
	 * @return void
	 */
	public function setTemplatePaths($templatePaths);

	/**
	 * @param string|NULL $configurationSectionName
	 * @return void
	 */
	public function setConfigurationSectionName($configurationSectionName);

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
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently displaying the record
	 * @return void
	 */
	public function preProcessRecord(array &$row, $id, DataHandler $reference);

	/**
	 * @abstract
	 * @param array $row The record data. Changing fields' values changes the record's values before display
	 * @return integer
	 */
	public function getPriority(array $row);

	/**
	 * Returns array($header, $content) preview chunks
	 *
	 * @abstract
	 * @param array $row The record data to be analysed for variables to use in a rendered preview
	 * @return array
	 */
	public function getPreview(array $row);

	/**
	 * Post-process record data for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $operation TYPO3 operation identifier, i.e. "update", "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row the record data, by reference. Changing fields' values changes the record's values just before saving
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently saving the record
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, DataHandler $reference);

	/**
	 * Post-process database operation for the table that this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $status TYPO3 operation identifier, i.e. "new" etc.
	 * @param integer $id The ID of the current record (which is sometimes now included in $row
	 * @param array $row The record's data, by reference. Changing fields' values changes the record's values just before saving after operation
	 * @param DataHandler $reference A reference to the \TYPO3\CMS\Core\DataHandling\DataHandler object that is currently performing the database operation
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference);

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param DataHandler $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @abstract
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference);

	/**
	 * Perform operations upon clearing cache(s)
	 *
	 * @param array $command
	 * @return void
	 */
	public function clearCacheCommand($command = array());

	/**
	 * Converts the contents of the provided row's Flux-enabled field,
	 * at the same time running through the inheritance tree generated
	 * by getInheritanceTree() in order to apply inherited values.
	 *
	 * @param array $row
	 * @return array
	 */
	public function getFlexFormValues(array $row);

	/**
	 * Gets an inheritance tree (ordered parent -> ... -> this record)
	 * of record arrays containing raw values.
	 *
	 * @param array $row
	 * @return array
	 */
	public function getInheritanceTree(array $row);

	/**
	 * @param array $row
	 * @return string
	 */
	public function getControllerExtensionKeyFromRecord(array $row);

	/**
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionFromRecord(array $row);

	/**
	 * @param array $row
	 * @return string
	 */
	public function getControllerActionReferenceFromRecord(array $row);

	/**
	 * @param Form $form
	 */
	public function setForm(Form $form);

	/**
	 * @param Grid $grid
	 */
	public function setGrid(Grid $grid);

	/**
	 * @return void
	 */
	public function reset();

}
