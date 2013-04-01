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
 * Grid Service
 *
 * Handles reading of Grid definition from Flux template files
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_GridService implements t3lib_Singleton {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $fluxService;

	/**
	 * @param Tx_Flux_Service_FluxService $fluxService
	 * @return void
	 */
	public function injectFluxService(Tx_Flux_Service_FluxService $fluxService) {
		$this->fluxService = $fluxService;
	}

	/**
	 * DEPRECATED: moved to FluxService, method left as proxy
	 * @return array
	 */
	public function getGridFromTemplateFile($templatePathAndFilename, array $variables = array(), $configurationSection = NULL, array $paths = array(), $extensionName = NULL) {
		return $this->fluxService->getGridFromTemplateFile($templatePathAndFilename, $variables, $configurationSection, $paths, $extensionName);
	}

}
