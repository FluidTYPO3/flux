<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Class that renders a selection field for Fluid FCE template selection
 *
 * @package	Fluidcontent
 * @subpackage Backend
 */
class Tx_Fluidcontent_Backend_ContentSelector {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 *
	 * @var Tx_Fluidcontent_Service_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexform;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->configurationService = $this->objectManager->get('Tx_Fluidcontent_Service_ConfigurationService');
		$this->flexform = $this->objectManager->get('Tx_Flux_Service_FlexForm');
	}

	/**
	 * Render a Flexible Content Element type selection field
	 *
	 * @param array $parameters
	 * @param mixed $parentObject
	 * @return string
	 */
	public function renderField(array &$parameters, &$parentObject) {
		$allTemplatePaths = $this->configurationService->getContentConfiguration();
		$name = $parameters['itemFormElName'];
		$value = $parameters['itemFormElValue'];
		$select = '<div><select name="' . htmlspecialchars($name) . '"  class="formField select" onchange="if (confirm(TBE_EDITOR.labels.onChangeAlert) && TBE_EDITOR.checkSubmit(-1)){ TBE_EDITOR.submitForm() };">' . LF;
		$select .= '<option value="">' . $GLOBALS['LANG']->sL('LLL:EXT:fluidcontent/Resources/Private/Language/locallang_db.xml:tt_content.tx_fed_fcefile', TRUE) . '</option>' . LF;
		foreach ($allTemplatePaths as $key => $templatePathSet) {
			#$files = Tx_Flux_Utility_Path::getFiles($templatePathSet['templateRootPath'], TRUE);
			$files = array();
			$files = t3lib_div::getAllFilesAndFoldersInPath($files, $templatePathSet['templateRootPath']);
			if (count($files) > 0) {
				if ($templatePathSet['label']) {
					$groupLabel = $templatePathSet['label'];
				} elseif (!t3lib_extMgm::isLoaded($key)) {
					$groupLabel = ucfirst($key);
				} else {
					$emConfigFile = t3lib_extMgm::extPath($key, 'ext_emconf.php');
					require $emConfigFile;
					$groupLabel = empty($EM_CONF['']['title']) ? ucfirst($key) : $EM_CONF['']['title'];
				}
				$select .= '<optgroup label="' . htmlspecialchars($groupLabel) . '">' . LF;
				foreach ($files as $templateFilename) {
					#\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($templateFilename);
					$fileRelPath = substr($templateFilename, strlen(PATH_site));
					$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedStandaloneView');
					$view->setTemplatePathAndFilename($templateFilename);
					try {
						$config =  $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', 'Configuration');
						$enabled = $config['enabled'];
						$label = $config['label'];
						if ($enabled !== FALSE) {
							$optionValue = $key . ':' . $fileRelPath;
							if (!$label) {
								$label = $templateFilename;
							}
							$translatedLabel = Tx_Extbase_Utility_Localization::translate($label, $key);
							if ($translatedLabel !== NULL) {
								$label = $translatedLabel;
							}
							$selected = ($optionValue === $value ? ' selected="selected"' : '');
							$select .= '<option value="' . htmlspecialchars($optionValue) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>' . LF;
						}
					} catch (Exception $e) {
						$select .= '<option value="">INVALID: ' . $fileRelPath . ' (Exception # ' . $e->getMessage() . ')</option>' . LF;
					}
				}
				$select .= '</optgroup>' . LF;
			}
		}
		$select .= '</select></div>' . LF;
		return $select;
	}

}
