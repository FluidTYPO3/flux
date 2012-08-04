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
 * Text field structure provider
 *
 * @package Flux
 * @subpackage Provider/Structure/Field
 */
class Tx_Flux_Provider_Structure_Field_TextStructureProvider extends Tx_Flux_Provider_Structure_AbstractStructureProvider implements Tx_Flux_Provider_StructureProviderInterface {

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param array $configuration
	 * @return array
	 */
	public function render($configuration) {
		$fieldConfiguration = array(
			'type' => 'text',
		);
		if ($configuration['defaultExtras'] === NULL) {
			$typoScript = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$defaultExtras = $typoScript['plugin.']['tx_flux.']['settings.']['flexform.']['rteDefaults'];
		} else {
			$defaultExtras = $configuration['defaultExtras'];
		}
		$fieldStructureArray = $this->getStandardFieldStructureArray($configuration, $fieldConfiguration);
		$fieldStructureArray['TCEforms']['defaultExtras'] = $defaultExtras;
		return $fieldStructureArray;
	}

}
