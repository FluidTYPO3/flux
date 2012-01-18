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
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
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
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManager $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 *
	 * @param string $templatePathAndFilename
	 * @param array $variables
	 * @param string|NULL $configurationSection
	 * @param array $paths
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, array $variables=array(), $configurationSection=NULL, array $paths=array()) {
		if (file_exists($templatePathAndFilename) === FALSE) {
			$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
		}
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
		$view->assignMultiple($variables);
		$stored = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', $configurationSection);
		return $stored['grid'];
	}

}
?>