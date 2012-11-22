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
	 * @var Tx_Flux_Service_Flexform
	 */
	protected $flexformService;

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
	public function injectFlexformService(Tx_Flux_Service_FlexForm $flexformService) {
		$this->flexformService = $flexformService;
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
		$this->flexformService->setContentObjectData($cObj->data);
		list ($extensionName, $filename) = explode(':', $cObj->data['tx_fed_fcefile']);
		$paths = $this->configurationService->getContentConfiguration($extensionName);
		$absolutePath = $paths['templateRootPath'] . '/' . $filename;
		$view->setLayoutRootPath($paths['layoutRootPath']);
		$view->setPartialRootPath($paths['partialRootPath']);
		$view->setTemplatePathAndFilename($absolutePath);
		$view->setControllerContext($this->controllerContext);
		$config = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', 'Configuration');
		$variables = $this->flexformService->getAllAndTransform($config['fields']);
		$variables['page'] = $GLOBALS['TSFE']->page;
		$variables['record'] = $cObj->data;
		$variables['contentObject'] = $cObj;
		$variables['settings'] = $this->settings;
		$view->assignMultiple($variables);
		return $view->render();
	}

}
