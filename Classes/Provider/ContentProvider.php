<?php
namespace FluidTYPO3\Flux\Provider;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ContentService;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ConfigurationProvider for records in tt_content
 *
 * This Configuration Provider has the lowest possible priority
 * and is only used to execute a set of hook-style methods for
 * processing records. This processing ensures that relationships
 * between content elements get stored correctly.
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
	 * @var ContentService
	 */
	protected $contentService;

	/**
	 * @param ContentService $contentService
	 * @return void
	 */
	public function injectContentService(ContentService $contentService) {
		$this->contentService = $contentService;
	}

	/**
	 * @param array $row
	 * @param string $table
	 * @param string $field
	 * @param string $extensionKey
	 * @return bool
	 */
	public function trigger(array $row, $table, $field, $extensionKey = NULL) {
		if ('tt_content' === $table && NULL === $field) {
			// This Provider will bypass checking for matched plugin-
			// and/or content type in the case where $field is NULL.
			// This case is triggered *once* per record from our
			// TCEMain class; subsequent calls all have a $field and
			// will pass through to the basic trigger() method.
			// Note for implementers: if you subclass this ContentProvider
			// in your own extension (which is perfectly valid to do!)
			// please consider if you must override the trigger() method
			// to ensure that your particular Provider only reacts when
			// users save records that your Provider actually supports.
			return TRUE;
		}
		return parent::trigger($row, $table, $field, $extensionKey);
	}

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
	 * @param string $operation
	 * @param integer $id
	 * @param array $row
	 * @param DataHandler $reference
	 * @param array $removals Allows overridden methods to pass an additional array of field names to remove from the stored Flux value
	 * @return void
	 */
	public function postProcessRecord($operation, $id, array &$row, DataHandler $reference, array $removals = array()) {
		if (TRUE === self::shouldCallWithClassName(__CLASS__, __FUNCTION__, $id, $operation)) {
			parent::postProcessRecord($operation, $id, $row, $reference, $removals);
			$parameters = GeneralUtility::_GET();
			$this->contentService->affectRecordByRequestParameters($id, $row, $parameters, $reference);
			self::trackMethodCallWithClassName(__CLASS__, __FUNCTION__, $id, $operation);
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
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
		if (TRUE === self::shouldCallWithClassName(__CLASS__, __FUNCTION__, $id, $command)) {
			parent::postProcessCommand($command, $id, $row, $relativeTo, $reference);
			$pasteCommands = array('copy', 'move');
			if (TRUE === in_array($command, $pasteCommands)) {
				$callback = $this->getCallbackCommand();
				if (TRUE === isset($callback['paste'])) {
					$pasteCommand = $callback['paste'];
					$parameters = explode('|', $pasteCommand);
					$this->contentService->pasteAfter($command, $row, $parameters, $reference);
				} else {
					$moveData = (array) $this->getMoveData();
					$this->contentService->moveRecord($row, $relativeTo, $moveData, $reference);
				}
			}
			if ('localize' === $command) {
				$this->contentService->fixPositionInLocalization($id, $relativeTo, $row, $reference);
			}
			self::trackMethodCallWithClassName(__CLASS__, __FUNCTION__, $id, $command);
		}
	}

	/**
	 * @return array|NULL
	 */
	protected function getMoveData() {
		$return = NULL;
		$rawPostData = $this->getRawPostData();
		if (FALSE === empty($rawPostData)) {
			$request = (array) json_decode($rawPostData, TRUE);
			$hasRequestData = TRUE === isset($request['method']) && TRUE === isset($request['data']);
			$isMoveMethod = 'moveContentElement' === $request['method'];
			$return = (TRUE === $hasRequestData && TRUE === $isMoveMethod) ? $request['data'] : NULL;
		}
		return $return;
	}

	/**
	 * @return array
	 */
	protected function getCallbackCommand() {
		$command = GeneralUtility::_GET('CB');
		return (array) $command;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function getRawPostData() {
		return file_get_contents('php://input');
	}

}
