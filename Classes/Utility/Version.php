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
 * Version utilities
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_Version {

	/**
	 * check for versions of TYPO3 which do not consistently pass $fieldName
	 *
	 * @return boolean
	 */
	public static function assertHasFixedFlexFormFieldNamePassing() {
		$version = explode('.', TYPO3_version);
		$isRecent4x5 = ($version[0] == 4 && $version[1] == 5 && $version[2] >= 23);
		$isRecent4x6 = ($version[0] == 4 && $version[1] == 6 && $version[2] >= 16);
		$isRecent4x7 = ($version[0] == 4 && $version[1] == 7 && $version[2] >= 8);
		$isRecent6x0 = self::assertCoreVersionIsAtLeastSixPointZero();
		return ($isRecent6x0 || $isRecent4x5 || $isRecent4x6 || $isRecent4x7);
	}

	/**
	 * @return boolean
	 */
	public static function assertCoreVersionIsBelowSixPointZero() {
		$version = explode('.', TYPO3_version);
		return ($version[0] < 6);
	}

	/**
	 * @return boolean
	 */
	public static function assertCoreVersionIsAtLeastSixPointZero() {
		$version = explode('.', TYPO3_version);
		return ($version[0] >= 6);
	}

}
