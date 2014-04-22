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

use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ConfigurationProvider for records in tt_content
 *
 * This Configuration Provider has the lowest possible priority
 * and is only used to execute a set of hook-style methods for
 * processing records. This processing ensures that relationships
 * between content elements get stored correctly.
 *
 * @package Flux
 * @subpackage Provider
 */
class ContentProvider extends AbstractProvider implements ProviderInterface {

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
	 * Note: This Provider will -always- trigger on tt_content list_type records (plugin)
	 * but has the lowest possible (0) priority, ensuring that any
	 * Provider which wants to take over, can do so.
	 *
	 * @param array $row
	 * @return integer
	 */
	public function getPriority(array $row) {
		if (FALSE === empty($row['list_type'])) {
			return 0;
		}
		return $this->priority;
	}

	/**
	 * Note: This Provider will -always- trigger on tt_content list_type records (plugin)
	 * but has the lowest possible (0) priority, ensuring that any
	 * Provider which wants to take over, can do so.
	 *
	 * @param array $row
	 * @param string $table
	 * @param string $field
	 * @param string $extensionKey
	 * @return boolean
	 */
	public function trigger(array $row, $table, $field, $extensionKey = NULL) {
		if ($table === $this->tableName && FALSE === empty($row['list_type'])) {
			return TRUE;
		}
		return parent::trigger($row, $table, $field, $extensionKey);
	}

	/*
	 * @param string $operation
	 * @param integer $id
	 * @param array $row
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, DataHandler $reference) {
		if (FALSE === self::shouldCallWithClassName(__CLASS__, __FUNCTION__, $id)) {
			return;
		}

		parent::postProcessRecord($operation, $id, $row, $reference);
		$parameters = GeneralUtility::_GET();
		$this->contentService->affectRecordByRequestParameters($id, $row, $parameters, $reference);

		self::trackMethodCallWithClassName(__CLASS__, __FUNCTION__, $id);
	}

	/**
	 * @param string $status
	 * @param integer $id
	 * @param array $row
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessDatabaseOperation($status, $id, &$row, DataHandler $reference) {
		if (FALSE === self::shouldCallWithClassName(__CLASS__, __FUNCTION__, $id)) {
			return;
		}

		parent::postProcessDatabaseOperation($status, $id, $row, $reference);
		if ($status === 'new') {
			$this->contentService->initializeRecord($id, $row, $reference);
		}

		self::trackMethodCallWithClassName(__CLASS__, __FUNCTION__, $id);
	}

	/**
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
		if (FALSE === self::shouldCallWithClassName(__CLASS__, __FUNCTION__, $id)) {
			return;
		}

		parent::postProcessCommand($command, $id, $row, $relativeTo, $reference);
		$pasteCommands = array('copy', 'move');
		if (TRUE === in_array($command, $pasteCommands)) {
			$callback = $this->getCallbackCommand();
			if (TRUE === isset($callback['paste'])) {
				$pasteCommand = $callback['paste'];
				$parameters = explode('|', $pasteCommand);
				$this->contentService->pasteAfter($command, $row, $parameters, $reference);
			} else {
				$moveData = $this->getMoveData();
				$this->contentService->moveRecord($row, $relativeTo, $moveData, $reference);
			}
		}

		self::trackMethodCallWithClassName(__CLASS__, __FUNCTION__, $id);
	}

	/**
	 * @return array
	 */
	protected function getCallbackCommand() {
		$command = GeneralUtility::_GET('CB');
		return (array) $command;
	}

	/**
	 * @return array
	 */
	protected function getMoveData() {
		$rawPostData = file_get_contents('php://input');
		if (FALSE === empty($rawPostData)) {
			$request = json_decode($rawPostData, TRUE);
			if (TRUE === isset($request['method']) && TRUE === isset($request['data']) && 'moveContentElement' === $request['method']) {
				return $request['data'];
			}
		}
		return NULL;
	}

}
