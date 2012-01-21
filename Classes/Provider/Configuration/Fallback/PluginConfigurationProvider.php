<?php

/* * *************************************************************
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
 * ************************************************************* */

/**
 * @package Flux
 * @subpackage Provider/Configuration/Fallback
 */
class Tx_Flux_Provider_Configuration_Fallback_PluginConfigurationProvider extends Tx_Flux_Provider_Configuration_Fallback_ConfigurationProvider implements Tx_Flux_Provider_PluginConfigurationProviderInterface {

	/**
	 * @var string|NULL
	 */
	protected $listType = NULL;

	/**
	 * @param string $listType
	 */
	public function setListType($listType) {
		$this->listType = $listType;
	}

	/**
	 *
	 * @param array $row
	 * @return type
	 */
	public function getListType(array $row) {
		return $this->listType;
	}

}

?>