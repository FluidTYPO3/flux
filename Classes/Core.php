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
 * Core integrations
 *
 * @package	TYPO3
 * @subpackage	fed
 */
class Tx_Fluidcontent_Core {

	const CACHED_CONTENT_ELEMENTS_FILE = 'typo3conf/.FED_CONTENT';

	/**
	 * @return void
	 */
	static function loadRegisteredFluidContentElementTypoScript() {
		$pageTsConfig = self::retrieveCachedConfiguration();
		if ($pageTsConfig === FALSE) {
			$pageTsConfig = self::writeCachedConfiguration();
		}
		if ($pageTsConfig !== FALSE) {
			t3lib_extMgm::addPageTSConfig($pageTsConfig);
		}
		unset($pageTsConfig);
	}

	/**
	 * @return string|boolean
	 */
	protected static function writeCachedConfiguration() {
		self::performWarmup();
		$fedWizardElements = array();
		$pageTsConfig = '';
		/** @var t3lib_tsparser_ext $template */
		$template = t3lib_div::makeInstance("t3lib_tsparser_ext");
		$template->tt_track = 0;
		$template->init();
		/** @var t3lib_pageSelect $sys_page */
		$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
		$pageUid = intval(t3lib_div::_GP('id'));
		if ($pageUid < 1) {
			return FALSE;
		}
		$rootLine = $sys_page->getRootLine($pageUid);
		$template->runThroughTemplates($rootLine);
		$template->generateConfig();
		$allTemplatePaths = $template->setup['plugin.']['tx_fed.']['fce.'];
		$allTemplatePaths = Tx_Flux_Utility_Path::translatePath($allTemplatePaths);
		if (is_array($allTemplatePaths) === FALSE) {
			return FALSE;
		}
		unset($GLOBALS['TYPO3_DB']);
		$wizardTabs = array();
		foreach ($allTemplatePaths as $key => $templatePathSet) {
			$key = trim($key, '.');
			$templateRootPath = t3lib_div::getFileAbsFileName($templatePathSet['templateRootPath']);
			$files = array();
			$files = t3lib_div::getAllFilesAndFoldersInPath($files, $templateRootPath, '');
			$defaultIcon = '../' . t3lib_extMgm::siteRelPath('fluidcontent') . 'Resources/Public/Icons/Plugin.png';
			$defaultTab = 'FCE';
			if (count($files) > 0) {
				foreach ($files as $templateFilename) {
					$fileRelPath = substr($templateFilename, strlen($templateRootPath));
					$contentConfiguration = array();
					$templateContents = file_get_contents($templateFilename);
					$matches = array();
					$pattern = '/<flux\:flexform[^\.]([^>]+)/';
					preg_match_all($pattern, $templateContents, $matches);
					$tabId = 'fed';
					foreach (explode('" ', trim($matches[1][0], '"')) as $valueStringPair) {
						list ($name, $value) = explode('="', trim($valueStringPair, '"'));
						$contentConfiguration[$name] = $value;
					}
					if ($contentConfiguration['enabled'] === 'FALSE') {
						continue;
					}
					if (!isset($contentConfiguration['wizardTab'])) {
						$wizardTabs[$tabId]['title'] = $defaultTab;
					} else {
						$tabId = self::sanitizeString($contentConfiguration['wizardTab']);
						$wizardTabs[$tabId]['title'] = $contentConfiguration['wizardTab'];
					}
					$id = $key . '_' . preg_replace('/[\.\/]/' , '', $fileRelPath);
					$pageTsConfig .= sprintf('
						mod.wizards.newContentElement.wizardItems.%s.elements.%s {
							icon = %s
							title = %s
							description = %s
							tt_content_defValues {
								CType = fed_fce
								tx_fed_fcefile = %s
							}
						}
						',
						$tabId,
						$id,
						($contentConfiguration['icon'] ? $contentConfiguration['icon'] : $defaultIcon) ,
						$contentConfiguration['label'],
						$contentConfiguration['description'],
						$key . ':' . $fileRelPath
					);
					$wizardTabs[$tabId]['elements'][] = $id;
				}
			}
		}
		foreach ($wizardTabs as $tabId => $tab) {
			$pageTsConfig .= sprintf('
				mod.wizards.newContentElement.wizardItems.%s {
					header = %s
					show = %s
					position = 0
				}
				',
				$tabId,
				$tab['title'],
				implode(',', $tab['elements'])
			);
		}
		t3lib_div::writeFile(PATH_site . self::CACHED_CONTENT_ELEMENTS_FILE, $pageTsConfig);
		self::performShutdown();
		return $pageTsConfig;
	}

	/**
	 * @return string|boolean
	 */
	protected static function retrieveCachedConfiguration() {
		$pageTsConfig = FALSE;
		if (file_exists(PATH_site . self::CACHED_CONTENT_ELEMENTS_FILE)) {
			return file_get_contents(PATH_site . self::CACHED_CONTENT_ELEMENTS_FILE);
		}
		return $pageTsConfig;
	}

	/**
	 * @return void
	 */
	protected static function performWarmup() {
		// Setting some global vars:
		$GLOBALS['EXEC_TIME'] = time();					// $EXEC_TIME is set so that the rest of the script has a common value for the script execution time
		$GLOBALS['SIM_EXEC_TIME'] = $GLOBALS['EXEC_TIME'];			// $SIM_EXEC_TIME is set to $EXEC_TIME but can be altered later in the script if we want to simulate another execution-time when selecting from eg. a database
		$GLOBALS['ACCESS_TIME'] = $GLOBALS['EXEC_TIME'] - ($GLOBALS['EXEC_TIME'] % 60);		// $ACCESS_TIME is a common time in minutes for access control
		$GLOBALS['SIM_ACCESS_TIME'] = $GLOBALS['ACCESS_TIME'];		// if $SIM_EXEC_TIME is changed this value must be set accordingly

		$GLOBALS['TYPO3_DB'] = new t3lib_DB();
		$GLOBALS['TYPO3_DB']->connectDB();
	}

	/**
	 * @return void
	 */
	protected static function performShutdown() {
		unset(
			$GLOBALS['SIM_EXEC_TIME'],
			$GLOBALS['ACCESS_TIME'],
			$GLOBALS['SIM_ACCESS_TIME'],
			$GLOBALS['TYPO3_DB']
		);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected static function sanitizeString($string) {
		$pattern = '/([^a-z0-9\-]){1,}/i';
		$string = preg_replace($pattern, '-', $string);
		return trim($string, '-');
	}

}
