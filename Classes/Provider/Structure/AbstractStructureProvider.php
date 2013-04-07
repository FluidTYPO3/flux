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
 * Base class for FlexForm XML structure providers
 *
 * @package Flux
 * @subpackage Provider/Structure
 */
class Tx_Flux_Provider_Structure_AbstractStructureProvider {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;

	/**
	 * @var Tx_Extbase_Object_ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @param array $configuration
	 * @return Tx_Flux_Provider_StructureProviderInterface
	 */
	protected function resolveFieldStructureProvider($configuration) {
		$structureProviderClassName = 'Tx_Flux_Provider_Structure_Field_' . $configuration['type'] . 'StructureProvider';
		$structureProviderClassName = class_exists($structureProviderClassName) ? $structureProviderClassName : 'Tx_Flux_Provider_Structure_Field_PassthroughStructureProvider';
		return $this->objectManager->create($structureProviderClassName);
	}

	/**
	 * @param array $configuration
	 * @return array
	 */
	protected function resolveStructureProviderAndRenderField($configuration) {
		return $this->resolveFieldStructureProvider($configuration)->render($configuration);
	}

	/**
	 * @param array $configuration
	 * @param array $fieldConfiguration
	 * @return array
	 */
	protected function getStandardFieldStructureArray($configuration, $fieldConfiguration) {
		$fieldStructureArray = array(
			'TCEforms' => array(
				'label' => $configuration['label'],
				'required' => $configuration['required'],
				'config' => $fieldConfiguration,
				'displayCond' => $configuration['displayCond']
			)
		);
		if ($configuration['wizards']) {
			$fieldStructureArray['TCEforms']['config']['wizards'] = $this->getWizardStructureArray($configuration);
		}
		if ($configuration['requestUpdate']) {
			$fieldStructureArray['TCEforms']['onChange'] = 'reload';
		}
		return $fieldStructureArray;
	}

	/**
	 * @param array $configuration
	 * @return array
	 */
	protected function getWizardStructureArray($configuration) {
		$wizardStructureArray = array();
		$wizards = t3lib_div::xml2array($configuration['wizards'], '', TRUE);
		if (isset($wizards['_DOCUMENT_TAG'])) {
			$wizards = array($wizards);
		}
		foreach ($wizards as $wizard) {
			$key = $wizard['_DOCUMENT_TAG'];
			$wizardStructureArray[$key] = $wizard;
		}
		return $wizardStructureArray;
	}

}