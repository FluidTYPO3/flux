<?php
namespace FluidTYPO3\Flux\Backend;
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

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Returns options for a "content area" selector box
 *
 * @package Flux
 * @subpackage Backend
 */
class AreaListItemsProcessor {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $fluxService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
	}

	/**
	 * ItemsProcFunc - adds items to tt_content.colPos selector (first, pipes through EXT:gridelements)
	 *
	 * @param array $params
	 * @return void
	 */
	public function itemsProcFunc(&$params) {
		$urlRequestedArea = $_GET['defVals']['tt_content']['tx_flux_column'];
		$urlRequestedParent = $urlRequestedValue = $_GET['defVals']['tt_content']['tx_flux_parent'];
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
		$uid = (integer) $uid;
		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('*', 'tt_content', "uid = '" . $uid . "'");
		/** @var $providers ProviderInterface[] */
		$providers = $this->fluxService->resolveConfigurationProviders('tt_content', NULL, $record);
		$columns = array();
		foreach ($providers as $provider) {
			$grid = $provider->getGrid($record);
			if (TRUE === empty($grid)) {
				continue;
			}
			$gridConfiguration = $grid->build();
			foreach ($gridConfiguration['rows'] as $row) {
				foreach ($row['columns'] as $column) {
					foreach ($column['areas'] as $area) {
						array_push($columns, array($area['label'] . ' (' . $area['name'] . ')', $area['name']));

					}
				}
			}
		}
		return array_unique($columns);
	}

}
