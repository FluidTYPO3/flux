<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Claus Due <claus@wildside.dk>, Wildside A/S
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
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * JSON Service
 *
 * Encodes and decodes JSON using optimal settings for mixed data types.
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_Grid implements t3lib_Singleton {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @param Tx_Extbase_Object_ObjectManager $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * Reads a Grid constructed using flux:flexform.grid, returning an array of
	 * defined rows and columns along with any content areas.
	 *
	 * Note about specific implementations:
	 *
	 * * EXT:fluidpages uses the Grid to render a BackendLayout on TYPO3 6.0 and above
	 * * EXT:flux uses the Grid to render content areas inside content elements
	 *   registered with Flux
	 *
	 * But your custom extension is of course allowed to use the Grid for any
	 * purpose. You can even read the Grid from - for example - the currently
	 * selected page template to know exactly how the BackendLayout looks.
	 *
	 * @param string $templatePathAndFilename
	 * @param array $variables
	 * @param string|NULL $configurationSection
	 * @param array $paths
	 * @return array
	 * @throws Exception
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, array $variables = array(), $configurationSection = NULL, array $paths = array()) {
		try {
			if (file_exists($templatePathAndFilename) === FALSE) {
				$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
			}
			if (file_exists($templatePathAndFilename) === FALSE) {
				t3lib_div::sysLog('Attempted to fetch a Grid from a template file which does not exist (' . $templatePathAndFilename . ')', 'flux', t3lib_div::SYSLOG_SEVERITY_WARNING);
				return array();
			}
			$paths = Tx_Flux_Utility_Path::translatePath($paths);
			$context = $this->objectManager->create('Tx_Extbase_MVC_Controller_ControllerContext');
			$request = $this->objectManager->create('Tx_Extbase_MVC_Request');
			$response = $this->objectManager->create('Tx_Extbase_MVC_Response');
			$request->setControllerExtensionName('Flux');
			$request->setControllerName('Flux');
			$request->setDispatched(TRUE);
			$context->setRequest($request);
			$context->setResponse($response);
			$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedTemplateView');
			$view->setControllerContext($context);
			$view->setTemplatePathAndFilename($templatePathAndFilename);
			if ($paths['partialRootPath']) {
				$view->setPartialRootPath($paths['partialRootPath']);
			}
			if ($paths['layoutRootPath']) {
				$view->setLayoutRootPath($paths['layoutRootPath']);
			}
			$view->assignMultiple($variables);
			$stored = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $configurationSection);
			$grid = isset($stored['grid']) ? $stored['grid'] : NULL;
		} catch (Exception $error) {
			if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
				throw $error;
			} else {
				t3lib_div::sysLog($error->getMessage(), 'flux');
			}
			$grid = array();
		}
		return $grid;
	}

}
