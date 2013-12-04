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
 * ConfigurationProvider for records in tt_content
 *
 * This Configuration Provider has the lowest possible priority
 * and is only used to execute a set of hook-style methods for
 * processing records. This processing ensures that relationships
 * between content elements get stored correctly -
 *
 * @package Flux
 * @subpackage Provider
 */
class Tx_Flux_Provider_ContentProvider extends Tx_Flux_Provider_AbstractProvider implements Tx_Flux_Provider_ProviderInterface {

	/**
	 * @var string
	 */
	protected $extensionKey = 'flux';

	/**
	 * @var integer
	 */
	protected $priority = 50;

	/**
	 * @var string
	 */
	protected $tableName = 'tt_content';

	/**
	 * @var string
	 */
	protected $fieldName = 'pi_flexform';

	/**
	 * @param string $operation
	 * @param integer $id
	 * @param array $row
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		parent::postProcessRecord($operation, $id, $row, $reference);
		$parameters = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET();
		$this->contentService->affectRecordByRequestParameters($row, $parameters, $reference);
		// note; hack-like pruning of an empty node that is inserted. Language handling in FlexForms combined with section usage suspected as cause
		if (empty($row['pi_flexform']) === FALSE && is_string($row['pi_flexform']) === TRUE) {
			$row['pi_flexform'] = str_replace('<field index=""></field>', '', $row['pi_flexform']);
		}
	}

	/**
	 * @param string $status
	 * @param integer $id
	 * @param array $row
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		parent::postProcessDatabaseOperation($status, $id, $row, $reference);
		if ($status === 'new') {
			$this->contentService->initializeRecord($row, $reference);
		}
	}

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, \TYPO3\CMS\Core\DataHandling\DataHandler $reference) {
		parent::postProcessCommand($command, $id, $row, $relativeTo, $reference);
		$pasteCommands = array('copy', 'move');
		if (TRUE === in_array($command, $pasteCommands)) {
			$callback = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('CB');
			if (TRUE === isset($callback['paste'])) {
				$pasteCommand = $callback['paste'];
				$parameters = explode('|', $pasteCommand);
				$this->contentService->pasteAfter($command, $row, $parameters, $reference);
			} else {
				$this->contentService->moveRecord($row, $relativeTo, $reference);
			}
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', "uid = '" . $id . "'", $row);
		}
	}

}
