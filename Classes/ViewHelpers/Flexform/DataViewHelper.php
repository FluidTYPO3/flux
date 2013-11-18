<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
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

/**
 * Converts raw flexform xml into an associative array
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Flexform_DataViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @var array
	 */
	private static $dataCache = array();

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;


	/**
	 * Inject Flux service
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * Render method
	 * @param integer $uid
	 * @param string $table
	 * @param string $field
	 * @param string $as
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return array
	 */
	public function render($uid, $table, $field, $as = NULL) {
		if (TRUE === isset(self::$dataCache[$uid.$table.$field])) {
		    $dataArray = self::$dataCache[$uid.$table.$field];
		} else {
			$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,' . $field, $table, sprintf('uid=%d', $uid));
			if (FALSE === $rows || 0 === count($rows)) {
				throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception(sprintf('Either table "%s", field "%s" or record with uid %d do not exist.', $table, $field, $uid), 1358679983);
			}
			$row = array_pop($rows);
			$providers = $this->configurationService->resolveConfigurationProviders($table, $field, $row);
			if (0 === count($providers)) {
				$dataArray = $this->configurationService->convertFlexFormContentToArray($row[$field]);
			} else {
				$dataArray = array();
				foreach ($providers as $provider) {
					$data = (array) $provider->getFlexFormValues($row);
					$dataArray = Tx_Flux_Utility_RecursiveArray::merge($dataArray, $data);
				}
			}
			self::$dataCache[$uid.$table.$field] = $dataArray;
		}
		if (NULL !== $as) {
			if ($this->templateVariableContainer->exists($as)) {
				$backupVariable = $this->templateVariableContainer->get($as);
				$this->templateVariableContainer->remove($as);
			}
			$this->templateVariableContainer->add($as, $dataArray);
			$content = $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			if (TRUE === isset($backupVariable)) {
				$this->templateVariableContainer->add($as, $backupVariable);
			}
			return $content;
		}
		return $dataArray;
	}
}
