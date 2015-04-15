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
 * LightweightProvider for moved records
 *
 * This providerish class jumps in if none more specific provider
 * was resolved AND a record was moved in backend.
 * It does not extend AbstractProvider because only a small subset
 * of methods is to be used for that purpose.
 *
 * @package Flux
 * @subpackage Provider
 */
class LightweightProvider {

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
	 * Post-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 * Slimmed down version of method from AbstractProvider
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param DataHandler $reference
	 * @return void
	 */
	public function postProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
		$moveData = $this->getMoveData();
		$this->contentService->moveRecord($row, $relativeTo, $moveData, $reference);
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
	 */
	protected function getRawPostData() {
		return file_get_contents('php://input');
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
	 * Only a stub for compatibility with extended AbstractProvider
	 *
	 * @param string $methodName
	 * @param mixed $id
	 * @return boolean
	 */
	public function shouldCall($methodName, $id) {
		return TRUE;
	}

	/**
	 * Pre-process a command executed on a record form the table this ConfigurationProvider
	 * is attached to.
	 *
	 * @param string $command
	 * @param integer $id
	 * @param array $row
	 * @param integer $relativeTo
	 * @param DataHandler $reference
	 * @return void
	 */
	public function preProcessCommand($command, $id, array &$row, &$relativeTo, DataHandler $reference) {
		unset($command, $id, $row, $relativeTo, $reference);
	}

	/**
	 * Only a stub for compatibility with extended AbstractProvider
	 *
	 * @param string $methodName
	 * @param mixed $id
	 * @return void
	 */
	public function trackMethodCall($methodName, $id) {
	}

}
