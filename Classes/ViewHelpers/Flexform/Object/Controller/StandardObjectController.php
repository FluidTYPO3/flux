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
 * Standard (shared) Flexform Object Controller
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Object
 */
class Tx_Flux_ViewHelpers_Flexform_Object_Controller_StandardObjectController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Tx_Fluid_View_StandaloneView';

	/**
	 * @var string
	 */
	protected $objectType;

	/**
	 * @var Tx_Fluid_View_StandaloneView
	 */
	protected $view;

	/**
	 * @var Tx_Fluid_Core_ViewHelper_TemplateVariableContainer
	 */
	protected $templateVariableContainer;

	/**
	 * @var Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer
	 */
	protected $viewHelperVariableContainer;

	/**
	 * @param string $objectType
	 */
	public function setObjectType($objectType) {
		$this->objectType = $objectType;
	}

	/**
	 * @param Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer $viewHelperVariableContainer
	 */
	public function setViewHelperVariableContainer($viewHelperVariableContainer) {
		$this->viewHelperVariableContainer = $viewHelperVariableContainer;
	}

	/**
	 * @param Tx_Fluid_Core_ViewHelper_TemplateVariableContainer $templateVariableContainer
	 */
	public function setTemplateVariableContainer($templateVariableContainer) {
		$this->templateVariableContainer = $templateVariableContainer;
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public function indexAction() {
		$typoscript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		$viewConfiguration = $typoscript['plugin.']['tx_flux.']['view.'];
		$templateFileName = $viewConfiguration['partialRootPath'] . 'Flexform/Object/' . $this->objectType . '.xml';
		$templatePathAndFilename = Tx_Flux_Utility_Path::translatePath($templateFileName);
		/** @var Tx_Fluid_Core_Rendering_RenderingContext $renderingContext */
		$renderingContext = $this->objectManager->get('Tx_Fluid_Core_Rendering_RenderingContext');
		$renderingContext->setControllerContext($this->controllerContext);
		if (method_exists($renderingContext, 'injectViewHelperVariableContainer') === FALSE) {
			throw new Exception('FlexForm section object Widgets are not supported on TYPO3 4.5', 1343612008);
		}
		$renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
		$renderingContext->injectTemplateVariableContainer($this->templateVariableContainer);
		$this->view->setRenderingContext($renderingContext);
		$this->view->setControllerContext($this->controllerContext);
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
		$this->view->assign('objectParameters', $this->widgetConfiguration);
		$this->view->assign('settings', Tx_Flux_Utility_Array::convertTypoScriptArrayToPlainArray($typoscript['plugin.']['tx_flux.']['settings.']));
		$content = $this->view->render();
		return $content;
	}
}
