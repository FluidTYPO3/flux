<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Björn Fromme <fromme@dreipunktnull.com>, dreipunktnull
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

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception;

/**
 * Converts raw flexform xml into an associative array
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class DataViewHelper extends AbstractViewHelper {

	/**
	 * @var array
	 */
	private static $dataCache = array();

	/**
	 * @var FluxService
	 */
	protected $configurationService;


	/**
	 * Inject Flux service
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * Render method
	 * @param string $table
	 * @param string $field
	 * @param integer $uid
	 * @param array $record
	 * @param string $as
	 * @return array
	 * @throws Exception
	 */
	public function render($table, $field, $uid = NULL, $record = NULL, $as = NULL) {
		if (NULL === $uid && NULL !== $record && TRUE === isset($record['uid'])) {
			$uid = $record['uid'];
		}
		if (TRUE === isset(self::$dataCache[$uid.$table.$field])) {
		    $dataArray = self::$dataCache[$uid.$table.$field];
		} elseif (TRUE === isset($GLOBALS['TCA'][$table]) && TRUE === isset($GLOBALS['TCA'][$table]['columns'][$field])) {
			if (NULL === $record) {
				$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('uid,' . $field, $table, sprintf('uid=%d', $uid));
			}
			if (FALSE === $record) {
				throw new Exception(sprintf('Either table "%s", field "%s" or record with uid %d do not exist and you did not manually ' .
					'provide the "row" attribute.', $table, $field, $uid), 1358679983);
			}
			$providers = $this->configurationService->resolveConfigurationProviders($table, $field, $record);
			if (0 === count($providers)) {
				$dataArray = $this->configurationService->convertFlexFormContentToArray($record[$field]);
			} else {
				$dataArray = array();
				foreach ($providers as $provider) {
					$data = (array) $provider->getFlexFormValues($record);
					$dataArray = RecursiveArrayUtility::merge($dataArray, $data);
				}
			}
			self::$dataCache[$uid.$table.$field] = $dataArray;
		} else {
			throw new Exception('Invalid table:field "' . $table . ':' . $field . '" - does not exist in TYPO3 TCA.', 1387049117);
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
