<?php
namespace FluidTYPO3\Flux\Backend;
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
 * @package Flux
 * @subpackage Backend
 */
class TceMain {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var \FluidTYPO3\Flux\Service\FluxService
	 */
	protected $configurationService;

	/**
	 * @var boolean
	 */
	static private $cachesCleared = FALSE;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->configurationService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
	}

	/**
	 * @param string $command The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $relativeTo Filled if command is relative to another element
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processCmdmap_preProcess(&$command, $table, $id, &$relativeTo, &$reference) {
		$record = array();
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->executeConfigurationProviderMethod('preProcessCommand', $table, $id, $record, $arguments, $reference);
	}

	/**
	 * @param string $command The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $relativeTo Filled if command is relative to another element
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processCmdmap_postProcess(&$command, $table, $id, &$relativeTo, &$reference) {
		$record = array();
		$arguments = array('command' => $command, 'id' => $id, 'row' => &$record, 'relativeTo' => &$relativeTo);
		$this->executeConfigurationProviderMethod('postProcessCommand', $table, $id, $record, $arguments, $reference);
	}

	/**
	 * @param array $incomingFieldArray The original field names and their values before they are processed
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, &$reference) {
		$arguments = array('row' => &$incomingFieldArray, 'id' => $id);
		$this->executeConfigurationProviderMethod('preProcessRecord', $table, $id, $incomingFieldArray, $arguments, $reference);
	}

	/**
	 * @param string $status The TCEmain operation status, fx. 'update'
	 * @param string $table The table TCEmain is currently processing
	 * @param string $id The records id (if any)
	 * @param array $fieldArray The field names and their values to be processed
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference Reference to the parent object (TCEmain)
	 * @return void
	 */
	public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, &$reference) {
		$arguments = array('status' => $status, 'id' => $id, 'row' => &$fieldArray);
		$this->executeConfigurationProviderMethod('postProcessRecord', $table, $id, $fieldArray, $arguments, $reference);
	}

	/**
	 * @param string $status The command which has been sent to processDatamap
	 * @param string $table	The table we're dealing with
	 * @param mixed $id Either the record UID or a string if a new record has been created
	 * @param array $fieldArray The record row how it has been inserted into the database
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference A reference to the TCEmain instance
	 * @return void
	 */
	public function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$reference) {
		$arguments = array('status' => $status, 'id' => $id, 'row' => &$fieldArray);
		$this->executeConfigurationProviderMethod('postProcessDatabaseOperation', $table, $id, $fieldArray, $arguments, $reference);
	}

	/**
	 * Wrapper method to execute a ConfigurationProvider
	 *
	 * @param string $methodName
	 * @param string $table
	 * @param mixed $id
	 * @param array $record
	 * @param array $arguments
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	protected function executeConfigurationProviderMethod($methodName, $table, $id, array &$record, array &$arguments, &$reference) {
		try {
			if (FALSE !== strpos($id, 'NEW')) {
				$id = $reference->substNEWwithIDs[$id];
			}
			$clause = "uid = '" . $id . "'";
			if (0 === count($record)) {
				// patch: when a record is completely empty but a UID exists
				$loadedRecord = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', $table, $clause);
				if (TRUE === is_array($loadedRecord)) {
					$record = array_pop($loadedRecord);
					$arguments['row'] = &$record;
				}
			}
			$arguments[] = &$reference;
			// check for a registered generic ConfigurationProvider for $table
			$detectedProviders = array();
			$providers = $this->configurationService->resolveConfigurationProviders($table, NULL, $record);
			foreach ($providers as $provider) {
				$class = get_class($provider);
				$detectedProviders[$class] = $provider;
			}
			// check each field for a registered ConfigurationProvider
			foreach ($record as $fieldName => $unusedValue) {
				$providers = $this->configurationService->resolveConfigurationProviders($table, $fieldName, $record);
				foreach ($providers as $provider) {
					$class = get_class($provider);
					$detectedProviders[$class] = $provider;
				}
			}
			foreach ($detectedProviders as $provider) {
				if (TRUE === $provider->shouldCall($methodName, $record)) {
					call_user_func_array(array($provider, $methodName), $arguments);
					$provider->trackMethodCall($methodName, $record);
				}
			}
		} catch (\Exception $error) {
			$this->configurationService->debug($error);
		}
	}

	/**
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @param string $command
	 * @return void
	 */
	public function clearCacheCommand($command) {
		if (TRUE === self::$cachesCleared) {
			return;
		}
		$tables = array_keys($GLOBALS['TCA']);
		foreach ($tables as $table) {
			$providers = $this->configurationService->resolveConfigurationProviders($table, NULL);
			foreach ($providers as $provider) {
				/** @var $provider \FluidTYPO3\Flux\Provider\ProviderInterface */
				$provider->clearCacheCommand($command);
			}
		}
		self::$cachesCleared = TRUE;
	}

}
