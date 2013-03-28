<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 * Abstract Flux-enabled controller
 *
 * Extends a traditional ActionController with new services and methods
 * to ease interaction with Flux forms. Is not required as subclass for
 * Controllers rendering records associated with Flux - all it does is
 * ease the interaction by providing a common API.
 *
 * @package Flux
 * @subpackage Controller
 * @route off
 */
class Tx_Flux_Controller_AbstractFluxController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'Tx_Flux_MVC_View_ExposedTemplateView';

	/**
	 * @var Tx_Flux_Provider_ConfigurationService
	 */
	protected $providerConfigurationService;

	/**
	 * @var Tx_Flux_Service_FlexForm
	 */
	protected $flexFormService;

	/**
	 * @var Tx_Flux_Service_Content
	 */
	protected $contentService;

	/**
	 * @var Tx_Flux_Service_Debug
	 */
	protected $debugService;

	/**
	 * @var Tx_Flux_Provider_ConfigurationProviderInterface
	 */
	protected $provider;

	/**
	 * @var string
	 */
	protected $fluxRecordField = 'pi_flexform';

	/**
	 * @var array
	 */
	private $setup = array();

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @param Tx_Flux_Service_FlexForm $flexFormService
	 * @return void
	 */
	public function injectFlexFormService(Tx_Flux_Service_FlexForm $flexformService) {
		$this->flexFormService = $flexformService;
	}

	/**
	 * @param Tx_Flux_Service_Content $contentService
	 */
	public function injectContentService(Tx_Flux_Service_Content $contentService) {
		$this->contentService = $contentService;
	}

	/**
	 * @param Tx_Flux_Provider_ConfigurationService $providerConfigurationService
	 * @return void
	 */
	public function injectProviderConfigurationService(Tx_Flux_Provider_ConfigurationService $providerConfigurationService) {
		$this->providerConfigurationService = $providerConfigurationService;
	}

	/**
	 * @param Tx_Flux_Service_Debug $debugService
	 * @return void
	 */
	public function injectDebugService(Tx_Flux_Service_Debug $debugService) {
		$this->debugService = $debugService;
	}

	/**
	 * @param Tx_Extbase_MVC_View_ViewInterface $view
	 *
	 * @return void
	 */
	public function initializeView(Tx_Extbase_MVC_View_ViewInterface $view) {
		$row = $this->configurationManager->getContentObject()->data;
		$table = $this->configurationManager->getContentObject()->getCurrentTable();
		$extensionName = $this->controllerContext->getRequest()->getControllerExtensionName();
		$fluxRecordField = $this->getFluxRecordField();
		$this->provider = $this->providerConfigurationService->resolvePrimaryConfigurationProvider($table, $fluxRecordField, $row);
		if (NULL === $this->provider) {
			throw new Exception('Unable to resolve a Flux ConfigurationProvider, but controller indicates it is a Flux-enabled Controller - ' .
				'this is a grave error and indicates that EXT: ' . $extensionName . 'itself is broken - or that EXT:' . $extensionName .
				' has been overridden by another implementation which is broken. The controller that caused this error was', 1358693007);
		}
		$this->setup = $this->provider->getTemplatePaths($row);
		$this->data = $this->provider->getFlexFormValues($row);
		$templatePathAndFilename = $this->provider->getTemplatePathAndFilename($row);
		$view->setTemplatePathAndFilename($templatePathAndFilename);
		$view->setLayoutRootPath($this->setup['layoutRootPath']);
		$view->setPartialRootPath($this->setup['partialRootPath']);
		$view->setTemplateRootPath($this->setup['templateRootPath']);
		$view->assignMultiple($this->data);
		$this->view = $view;
	}

	/**
	 * Get the data stored in a record's Flux-enabled field,
	 * i.e. the variables of the Flux template as configured in this
	 * particular record.
	 *
	 * @return array
	 */
	protected function getData() {
		return $this->data;
	}

	/**
	 * Get the array of TS configuration associated with the
	 * Flux template of the record (or overall record type)
	 * currently being rendered.
	 *
	 * @return array
	 */
	protected function getSetup() {
		return $this->setup;
	}

	/**
	 * @return string
	 */
	protected function getFluxRecordField() {
		return $this->fluxRecordField;
	}

}