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

/**
 * Flexible Content Element Plugin Rendering Controller
 *
 * @package Fluidcontent
 * @subpackage Controller
 * @route off
 */
class Tx_Fluidcontent_Controller_ContentController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_Fluidcontent_Service_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * @param Tx_Fluidcontent_Service_ConfigurationService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Fluidcontent_Service_ConfigurationService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Tx_Flux_Service_FlexForm $flexformService
	 * @return void
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexFormService) {
		$this->flexFormService = $flexFormService;
	}

	/**
	 * Show template as defined in flexform
	 * @return string
	 * @route off
	 */
	public function renderAction() {
		/** @var $view Tx_Flux_MVC_View_ExposedTemplateView */
		$view = $this->objectManager->create('Tx_Flux_MVC_View_ExposedTemplateView');
		$cObj = $this->configurationManager->getContentObject();
		if (isset($cObj->data['tx_fed_fcefile']) === FALSE) {
			return 'Fluid Content type not selected';
		}
		$this->flexFormService->setContentObjectData($cObj->data);
		list ($extensionName, $filename) = explode(':', $cObj->data['tx_fed_fcefile']);
		if (empty($extensionName) || empty($filename)) {
			return 'Invalid Fluid Content type definition! The specified type is an empty value.';
		}
		$paths = $this->configurationService->getContentConfiguration($extensionName);
		$absolutePath = $paths['templateRootPath'] . '/' . $filename;
		if (is_file($absolutePath) === FALSE) {
			$safeReportPath = str_replace(PATH_site, '$PATH_site/', $absolutePath);
			return 'Fluid Content template file "' . $safeReportPath . '" does not exist';
		}
		if (is_dir($paths['layoutRootPath']) === FALSE) {
			return 'Fluid Content group has not defined a <code>layoutRootPath</code> - please make sure one is defined.
			 		If the group does not require Partials please use path <code>EXT:fluidcontent/Resources/Private/Partials</code>
			 		as a safe fallback path which is guaranteed to exist.';
		}
		if (is_dir($paths['partialRootPath']) === FALSE) {
			return 'Fluid Content group has not defined a <code>partialRootPath</code> - please make sure one is defined.
			 		If the group does not require Partials please use path <code>EXT:flux/Resources/Private/Partials</code>
			 		as a safe fallback path which is guaranteed to exist.';
		}
		$view->setLayoutRootPath($paths['layoutRootPath']);
		$view->setPartialRootPath($paths['partialRootPath']);
		$view->setTemplatePathAndFilename($absolutePath);
		$view->setControllerContext($this->controllerContext);
		$config = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', 'Configuration');
		$variables = $this->flexFormService->getAllAndTransform($config['fields']);
		$variables['page'] = $GLOBALS['TSFE']->page;
		$variables['record'] = $cObj->data;
		$variables['contentObject'] = $cObj;
		$variables['settings'] = $this->settings;
		$view->assignMultiple($variables);
		return $view->render();
	}

}
