<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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

require_once t3lib_extMgm::extPath('cms', 'layout/class.tx_cms_layout.php');
require_once t3lib_extMgm::extPath('cms', 'layout/interfaces/interface.tx_cms_layout_tt_content_drawitemhook.php');

/**
 * Fluid Template preview renderer
 *
 * @package Flux
 * @subpackage Backend
 */
class Tx_Flux_Backend_Preview implements tx_cms_layout_tt_content_drawItemHook {

	/**
	 * @var Tx_Extbase_Object_ObjectManager
	 */
	protected $objectManager;

	/**
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $view;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var Tx_Flux_Service_Json
	 */
	protected $jsonService;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexform;

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $configurationService;

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
		$this->jsonService = $this->objectManager->get('Tx_Flux_Service_Json');
		$this->configurationManager = $this->objectManager->get('Tx_Extbase_Configuration_ConfigurationManagerInterface');
		$this->flexform = $this->objectManager->get('Tx_Flux_Service_FlexForm');
		$this->configurationService = $this->objectManager->get('Tx_Flux_Provider_ConfigurationService');
		$this->view = $this->objectManager->get('Tx_Fluid_View_StandaloneView');
	}

	/**
	 *
	 * @param tx_cms_layout $parentObject
	 * @param boolean $drawItem
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @return void
	 */
	public function preProcess(tx_cms_layout &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		unset($parentObject, $drawItem);
		$this->renderPreview($headerContent, $itemContent, $row);
	}

	/**
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @return void
	 * @throws Exception
	 */
	public function renderPreview(&$headerContent, &$itemContent, array &$row) {
		$version = explode('.', TYPO3_version);
		$isRecent4x5 = ($version[0] == 4 && $version[1] == 5 && $version[2] >= 21);
		$isRecent4x6 = ($version[0] == 4 && $version[1] == 6 && $version[2] >= 14);
		$isRecent4x7 = ($version[0] == 4 && $version[1] == 7 && $version[2] >= 6);
		$isAbove4 = ($version[0] > 4);
		if ($isRecent4x5 === FALSE || $isRecent4x6 === FALSE || $isRecent4x7 === FALSE || $isAbove4 === FALSE) {
			$fieldName = NULL;
		} else {
			$fieldName = 'pi_flexform';
		}
		$providers = $this->configurationService->resolveConfigurationProviders('tt_content', $fieldName, $row);
		foreach ($providers as $provider) {
			/** @var Tx_Flux_Provider_ConfigurationProviderInterface $provider */
			$templatePathAndFilename = $provider->getTemplatePathAndFilename($row);
			if (file_exists($templatePathAndFilename) === FALSE) {
				$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
			}
			if (file_exists($templatePathAndFilename)) {
				$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
				$extension = str_replace('_', '', $provider->getExtensionKey($row));
				if ($provider->getTemplatePaths($row)) {
					$paths = $provider->getTemplatePaths($row);
				} else if (t3lib_extMgm::isLoaded($provider->getExtensionKey($row))) {
					$paths = $typoScript['plugin.']['tx_' . $extension . '.']['view.'];
				} else {
					$paths = $typoScript['plugin.']['tx_flux.']['view.'];
				}
				$paths = Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray($paths);
				try {
					$this->flexform->setContentObjectData($row);
					/** @var Tx_Extbase_MVC_Controller_ControllerContext $context */
					$context = $this->objectManager->create('Tx_Extbase_MVC_Controller_ControllerContext');
					/** @var Tx_Extbase_MVC_Request $request */
					$request = $this->objectManager->create('Tx_Extbase_MVC_Request');
					$response = $this->objectManager->create('Tx_Extbase_MVC_Response');
					$request->setControllerExtensionName('Flux');
					$request->setControllerName('Flux');
					$request->setDispatched(TRUE);
					$context->setRequest($request);
					$context->setResponse($response);
					$flexform = $this->flexform->getAll();
					/** @var Tx_Flux_MVC_View_ExposedTemplateView $view */
					$view = $this->objectManager->get('Tx_Flux_MVC_View_ExposedTemplateView');
					$view->setControllerContext($context);
					$view->setTemplatePathAndFilename($templatePathAndFilename);
					$view->assignMultiple($flexform);
					$view->assignMultiple((array) $provider->getTemplateVariables($row));
					$view->assign('row', $row);

					$stored = $view->getStoredVariable('Tx_Flux_ViewHelpers_FlexformViewHelper', 'storage', 'Configuration');
					$variables = array_merge($stored, (array) $provider->getTemplateVariables($row), $this->flexform->getAllAndTransform($stored['fields']));
					$label = Tx_Extbase_Utility_Localization::translate($stored['label'], $extension);
					if ($label === NULL) {
						$label = $stored['label'];
					}
					$variables['label'] = $label;
					$variables['config'] = $stored;
					$variables['row'] = $row;
					$view->setPartialRootPath(t3lib_div::getFileAbsFileName($paths['partialRootPath']));
					$view->setLayoutRootPath(t3lib_div::getFileAbsFileName($paths['layoutRootPath']));
					$view->setTemplatePathAndFilename($templatePathAndFilename);
					$itemContent .= $view->renderStandaloneSection('Preview', $variables);
					$headerContent .= '<div><strong>' . $label . '</strong> <i>' . $row['header'] . '</i></div>';
				} catch (Exception $e) {
					if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
						throw $e;
					} else {
						$itemContent .= 'Error: ' . $e->getMessage();
						$itemContent .= '<br />Code: ' . $e->getCode();
						$itemContent .= '<br />Filename: ' . $templatePathAndFilename;
					}
				}
			}
		}
	}

}
