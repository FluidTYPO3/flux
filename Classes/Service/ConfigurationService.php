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
	public function loadRegisteredFluidContentElementTypoScript() {
		if (file_exists(PATH_site . 'typo3conf/.FED_CONTENT') === FALSE) {
			$this->writeCachedConfiguration();
		}
	}

	/**
	 * @return void
	 */
	protected function writeCachedConfiguration() {
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
		$wizardTabs = array();
		foreach ($allTemplatePaths as $key => $templatePathSet) {
			$key = trim($key, '.');
			$templateRootPath = t3lib_div::getFileAbsFileName($templatePathSet['templateRootPath']);
			$files = array();
			$files = t3lib_div::getAllFilesAndFoldersInPath($files, $templateRootPath, '');
			$defaultIcon = '../' . t3lib_extMgm::siteRelPath('fluidcontent') . 'Resources/Public/Icons/Plugin.png';
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
					if (isset($contentConfiguration['wizardTab'])) {
						$tabId = $this->sanitizeString($contentConfiguration['wizardTab']);
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
		t3lib_div::writeFile(PATH_site . 'typo3conf/.FED_CONTENT', $pageTsConfig);
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
