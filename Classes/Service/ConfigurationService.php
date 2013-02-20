<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Configuration Service
 *
 * Provides methods to read various configuration related
 * to Fluid Content Elements.
 *
 * @author Claus Due, Wildside A/S
 * @package Fluidcontent
 * @subpackage Service
 */
class Tx_Fluidcontent_Service_ConfigurationService extends Tx_Flux_Service_Configuration implements t3lib_Singleton {

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * @var string
	 */
	protected $defaultIcon;

	/**
	 * @param Tx_Flux_Service_FlexForm $flexFormService
	 * @return void
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexFormService) {
		$this->flexFormService = $flexFormService;
	}

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->defaultIcon = '../' . t3lib_extMgm::siteRelPath('fluidcontent') . 'Resources/Public/Icons/Plugin.png';
	}

	/**
	 * Get definitions of paths for FCEs defined in TypoScript
	 *
	 * @param string $extensionName Optional extension name to get only that extension
	 * @return array
	 * @api
	 */
	public function getContentConfiguration($extensionName = NULL) {
		return $this->getTypoScriptSubConfiguration($extensionName, 'fce', array('label', 'dependencies'));
	}


	/**
	 * @return void
	 */
	public function writeCachedConfigurationIfMissing() {
		if (TRUE === file_exists(FLUIDCONTENT_TEMPFILE)) {
			return;
		}
		$pageUid = intval(t3lib_div::_GP('id'));
		if ($pageUid < 1) {
			$firstPageWithRootTemplate = array_shift($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('pid', 'sys_template t', "t.root = 1 AND deleted = 0 AND hidden = 0  AND starttime<=NOW() AND (endtime=0 OR endtime>NOW())"));
			if (TRUE === is_array($firstPageWithRootTemplate)) {
				$pageUid = $firstPageWithRootTemplate['pid'];
			} else {
				return FALSE;
			}
		}
		/** @var t3lib_tsparser_ext $template */
		$template = t3lib_div::makeInstance('t3lib_tsparser_ext');
		$template->tt_track = 0;
		$template->init();
		/** @var t3lib_pageSelect $sys_page */
		$sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$rootLine = $sys_page->getRootLine($pageUid);
		$template->runThroughTemplates($rootLine);
		$template->generateConfig();
		$allTemplatePaths = $template->setup['plugin.']['tx_fed.']['fce.'];
		$allTemplatePaths = Tx_Flux_Utility_Path::translatePath($allTemplatePaths);
		if (is_array($allTemplatePaths) === FALSE) {
			return FALSE;
		}
		$wizardTabs = $this->buildAllWizardTabGroups($allTemplatePaths);
		$pageTsConfig = $this->buildAllWizardTabsPageTsConfig($wizardTabs);
		t3lib_div::writeFile(FLUIDCONTENT_TEMPFILE, $pageTsConfig);
	}

	/**
	 * Scans all folders in $allTemplatePaths for template
	 * files, reads information about each file and collects
	 * the groups of files into groups of pageTSconfig setup.
	 *
	 * @param array $allTemplatePaths
	 * @return array
	 */
	protected function buildAllWizardTabGroups($allTemplatePaths) {
		$wizardTabs = array();
		foreach ($allTemplatePaths as $key => $templatePathSet) {
			$key = trim($key, '.');
			$templatePathSet = Tx_Flux_Utility_Path::translatePath($templatePathSet);
			$templateRootPath = $templatePathSet['templateRootPath'];
			$files = array();
			$files = t3lib_div::getAllFilesAndFoldersInPath($files, $templateRootPath, '');
			if (count($files) > 0) {
				foreach ($files as $templateFilename) {
					$fileRelPath = substr($templateFilename, strlen($templateRootPath));
					$contentConfiguration = $this->flexFormService->getFlexFormConfigurationFromFile($templateFilename, array(), 'Configuration', $templatePathSet);
					if ($contentConfiguration['enabled'] === 'FALSE') {
						continue;
					}
					if (isset($contentConfiguration['wizardTab'])) {
						$tabId = $this->sanitizeString($contentConfiguration['wizardTab']);
						$wizardTabs[$tabId]['title'] = $contentConfiguration['wizardTab'];
					}
					$id = $key . '_' . preg_replace('/[\.\/]/', '_', $fileRelPath);
					$elementTsConfig = $this->buildWizardTabItem($tabId, $id, $contentConfiguration, $key . ':' . $fileRelPath);
					$wizardTabs[$tabId]['elements'][$id] = $elementTsConfig;
				}
			}
		}
		return $wizardTabs;
	}

	/**
	 * Builds a big piece of pageTSconfig setup, defining
	 * every detected content element's wizard tabs and items.
	 *
	 * @param array $wizardTabs
	 * @return string
	 */
	protected function buildAllWizardTabsPageTsConfig($wizardTabs) {
		$pageTsConfig = '';
		foreach ($wizardTabs as $tab) {
			foreach ($tab['elements'] as $id => $elementTsConfig) {
				$pageTsConfig .= $elementTsConfig;
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
				implode(',', array_keys($tab['elements']))
			);
		}
		return $pageTsConfig;
	}

	/**
	 * Builds a single Wizard item (one FCE) based on the
	 * tab id, element id, configuration array and special
	 * template identity (groupName:Relative/Path/File.html)
	 *
	 * @param string $tabId
	 * @param string $id
	 * @param array $contentConfiguration
	 * @param string $templateFileIdentity
	 * @return string
	 */
	protected function buildWizardTabItem($tabId, $id, $contentConfiguration, $templateFileIdentity) {
		$iconFileRelativePath = ($contentConfiguration['icon'] ? $contentConfiguration['icon'] : $this->defaultIcon);
		return sprintf('
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
			$iconFileRelativePath,
			$contentConfiguration['label'],
			$contentConfiguration['description'],
			$templateFileIdentity
		);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	protected function sanitizeString($string) {
		$pattern = '/([^a-z0-9\-]){1,}/i';
		$string = preg_replace($pattern, '-', $string);
		return trim($string, '-');
	}

}
