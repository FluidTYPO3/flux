<?php
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 *****************************************************************/

/**
 * Content object configuration provider
 *
 * @author Claus Due <claus@wildside.dk>, Wildside A/S
 * @package Fluidcontent
 * @subpackage Provider
 */
class Tx_Fluidcontent_Provider_ContentConfigurationProvider extends Tx_Flux_Provider_AbstractContentObjectConfigurationProvider implements Tx_Flux_Provider_ContentObjectConfigurationProviderInterface {

	/**
	 * @var string
	 */
	protected $tableName = 'tt_content';

	/**
	 * @var string
	 */
	protected $fieldName = 'pi_flexform';

	/**
	 * @var string
	 */
	protected $extensionKey = 'fluidcontent';

	/**
	 * @var string
	 */
	protected $contentObjectType = 'fed_fce';

	/**
	 * @var string
	 */
	protected $configurationSectionName = 'Configuration';

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Fluidcontent_Service_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param Tx_Fluidcontent_Service_ConfigurationService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Fluidcontent_Service_ConfigurationService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param array $row
	 * @return string
	 */
	public function getTemplatePathAndFilename(array $row) {
		$templatePathAndFilename = $row['tx_fed_fcefile'];
		if (strpos($templatePathAndFilename, ':') === FALSE) {
			return NULL;
		}
		list ($extensionName, $filename) = explode(':', $templatePathAndFilename);
		$paths = $this->getTemplatePaths($row);
		if ($paths === NULL) {
			return NULL;
		}
		$templatePathAndFilename = Tx_Flux_Utility_Path::translatePath($paths['templateRootPath'] . $filename);
		return $templatePathAndFilename;
	}

	/**
	 * @param array $row
	 * @return array
	 */
	public function getTemplateVariables(array $row) {
		$templatePathAndFilename = $row['tx_fed_fcefile'];
		$filename = array_pop(explode(':', $templatePathAndFilename));
		$paths = $this->getTemplatePaths($row);
		if ($paths === NULL) {
			return NULL;
		}
		$templatePathAndFilename = Tx_Flux_Utility_Path::translatePath($paths['templateRootPath'] . $filename);
		$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedStandaloneView');
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		$this->flexFormService->setContentObjectData($row);
		$flexform = $this->flexFormService->getAll();
		$view->assignMultiple($flexform);
		$view->assignMultiple($this->flexFormService->setContentObjectData($row)->getAll());
		try {
			$stored = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', 'Configuration');
			$stored['sheets'] = array();
			foreach ($stored['fields'] as $field) {
				$groupKey = $field['sheets']['name'];
				$groupLabel = $field['sheets']['label'];
				if (is_array($stored['sheets'][$groupKey]) === FALSE) {
					$stored['sheets'][$groupKey] = array(
						'name' => $groupKey,
						'label' => $groupLabel,
						'fields' => array()
					);
				}
				array_push($stored['sheets'][$groupKey]['fields'], $field);
			}
			return $stored;
		} catch (Exception $e) {
			t3lib_div::sysLog('Fluid Content Element error: ' . $e->getMessage(), 'fluidcontent');
			return NULL;
		}
	}

	/**
	 * @param array $row
	 * @return array
	 */
	public function getTemplatePaths(array $row) {
		$templatePathAndFilename = $row['tx_fed_fcefile'];
		$extensionName = array_shift(explode(':', $templatePathAndFilename));
		$paths = $this->configurationService->getContentConfiguration($extensionName);
		if ($this->configurationService->checkDependenciesForConfiguration($paths) === FALSE) {
			return NULL;
		}
		return $paths;
	}

	/**
	 * Perform various cleanup operations upon clearing cache
	 *
	 * @return void
	 */
	public function clearCacheCommand() {
		if (file_exists(PATH_site . 'typo3conf/.FED_CONTENT') === TRUE) {
			unlink(PATH_site . 'typo3conf/.FED_CONTENT');
		}
	}

}
