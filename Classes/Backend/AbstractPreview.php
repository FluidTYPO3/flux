<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

if (TRUE === file_exists(t3lib_extMgm::extPath('cms', 'layout/class.tx_cms_layout.php'))) {
	require_once t3lib_extMgm::extPath('cms', 'layout/class.tx_cms_layout.php');
}
if (TRUE === file_exists(t3lib_extMgm::extPath('cms', 'layout/interfaces/interface.tx_cms_layout_tt_content_drawitemhook.php'))) {
	require_once t3lib_extMgm::extPath('cms', 'layout/interfaces/interface.tx_cms_layout_tt_content_drawitemhook.php');
}

/**
 * Fluid Template preview renderer
 *
 * @package Flux
 * @subpackage Backend
 */
abstract class Tx_Flux_Backend_AbstractPreview implements tx_cms_layout_tt_content_drawItemHook {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Service_FluxService');
	}

	/**
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @param boolean $drawItem
	 * @return void
	 * @throws Exception
	 */
	public function renderPreview(&$headerContent, &$itemContent, array &$row, &$drawItem) {
		$fieldName = 'pi_flexform';
		if ('shortcut' === $row['CType'] && FALSE === strpos($row['records'], ',')) {
			$targetRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('p.title, t.pid', 'tt_content t, pages p', "t.uid = '" . $row['records'] . "' AND p.uid = t.pid");
			$targetRecord = array_pop($targetRecords);
			$title = Tx_Extbase_Utility_Localization::translate('reference', 'Flux', array(
				$targetRecord['title']
			));
			$targetLink = '?id=' . $targetRecord['pid'] . '#c' . $row['records'];
			$iconClass = 't3-icon t3-icon-actions-insert t3-icon-insert-reference t3-icon-actions t3-icon-actions-insert-reference';
			$icon = '<a title="' . $title . '" href="' . $targetLink . '"><span class="' . $iconClass . '"></spa></a>';
			$itemContent = $icon . $itemContent;
		}
		$itemContent = '<a name="c' . $row['uid'] . '"></a>' . $itemContent;
		$providers = $this->configurationService->resolveConfigurationProviders('tt_content', $fieldName, $row);
		foreach ($providers as $provider) {
			/** @var Tx_Flux_Provider_ProviderInterface $provider */
			list ($previewHeader, $previewContent) = $provider->getPreview($row);
			if (FALSE === empty($previewHeader)) {
				$drawItem = FALSE;
				$headerContent .= '<div><strong>' . $previewHeader . '</strong> <i>' . $row['header'] . '</i></div>';
			}
			if (FALSE === empty($previewContent)) {
				$drawItem = FALSE;
				$itemContent .= $previewContent;
			}
		}
	}

}
