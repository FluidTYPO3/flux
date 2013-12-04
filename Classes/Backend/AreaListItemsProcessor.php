<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Returns options for a "content area" selector box
 *
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_AreaListItemsProcessor {

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $fluxService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$this->fluxService = $this->objectManager->get('Tx_Flux_Service_FluxService');
	}

	/**
	 * ItemsProcFunc - adds items to tt_content.colPos selector (first, pipes through EXT:gridelements)
	 *
	 * @param array $params
	 * @return void
	 */
	public function itemsProcFunc(&$params) {
		$urlRequestedArea = $this->getUrlRequestedArea();
		$urlRequestedParent = $urlRequestedValue = $this->getUrlRequestedParent();
		if ($urlRequestedParent) {
			$parentUid = $urlRequestedParent;
		} else {
			$parentUid = $params['row']['tx_flux_parent'];
		}
		if ($parentUid > 0) {
			$items = $this->getContentAreasDefinedInContentElement($parentUid);
		} else {
			$items = array();
		}
		array_unshift($items, array('', '')); // adds an empty option in the beginning of the item list
		if ($urlRequestedArea) {
			foreach ($items as $index => $set) {
				if ($set[0] !== $urlRequestedArea) {
					unset($items[$index]);
				}
			}
		}
		$params['items'] = $items;
	}

	/**
	 * @param integer $uid
	 * @return array
	 */
	public function getContentAreasDefinedInContentElement($uid) {
		$record = $this->getContentRecordByUid($uid);
		/** @var $provider Tx_Flux_Provider_ProviderInterface */
		$provider = $this->fluxService->resolvePrimaryConfigurationProvider('tt_content', NULL, $record);
		if (NULL === $provider) {
			return array();
		}
		return $this->getGridFromConfigurationProviderAndRecord($provider, $record);
	}

	/**
	 * @return string
	 */
	protected function getUrlRequestedArea() {
		return $_GET['defVals']['tt_content']['tx_flux_column'];
	}

	/**
	 * @return string
	 */
	protected function getUrlRequestedParent() {
		return $_GET['defVals']['tt_content']['tx_flux_parent'];
	}

	/**
	 * @param integer $uid
	 * @return array
	 */
	protected function getContentRecordByUid($uid) {
		$record = $this->loadContentRecordFromDatabase($uid);
		return (array) $record;
	}

	/**
	 * @param integer $uid
	 * @return array|FALSE
	 */
	protected function loadContentRecordFromDatabase($uid) {
		$uid = intval($uid);
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', "uid = '" . $uid . "'");
		return $record;
	}

	/**
	 * @param Tx_Flux_Provider_ProviderInterface $provider
	 * @param array $record
	 * @return mixed
	 */
	protected function getGridFromConfigurationProviderAndRecord(Tx_Flux_Provider_ProviderInterface $provider, array $record) {
		$columns = array();
		$grid = $provider->getGrid($record);
		foreach ($grid->getRows() as $row) {
			foreach ($row->getColumns() as $column) {
				foreach ($column->getAreas() as $area) {
					array_push($columns, array($area->getLabel(), $area->getName()));
				}
			}
		}
		return $columns;
	}

}
