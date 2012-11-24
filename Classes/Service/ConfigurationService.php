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
 * Configuration Service
 *
 * Provides methods to read various configuration related
 * to Fluid Content Elements.
 *
 * @author Claus Due, Wildside A/S
 * @package Fluidcontent
 * @subpackage Service
 */
class Tx_Fluidcontent_Service_ConfigurationService extends Tx_Flux_Service_Configuration implements t3lib_Singleton {

	/**
	 * Get definitions of paths for FCEs defined in TypoScript
	 *
	 * @param string $extensionName Optional extension name to get only that extension
	 * @return array
	 * @api
	 */
	public function getContentConfiguration($extensionName = NULL) {
		return $this->getTypoScriptSubConfiguration($extensionName, 'fce', array('label', 'dependencies'));
	}

}
