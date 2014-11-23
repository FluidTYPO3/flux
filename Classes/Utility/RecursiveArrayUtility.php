<?php
namespace FluidTYPO3\Flux\Utility;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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

use TYPO3\CMS\Core\Utility\ArrayUtility;

/**
 * RecursiveArray Utility
 *
 * @package Flux
 * @subpackage Utility
 */
class RecursiveArrayUtility {

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function merge($array1, $array2) {
		$array1 = (array) $array1;
		$array2 = (array) $array2;
		foreach ($array2 as $key => $val) {
			if (is_array($array1[$key])) {
				if (is_array($array2[$key])) {
					$val = self::merge($array1[$key], $array2[$key]);
				}
			}
			$array1[$key] = $val;
		}
		reset($array1);
		return $array1;
	}

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function diff($array1, $array2) {
		$array1 = (array) $array1;
		$array2 = (array) $array2;
		foreach ($array1 as $key => $value) {
			if (TRUE === isset($array2[$key])) {
				if (TRUE === is_array($value) && TRUE === is_array($array2[$key])) {
					$diff = self::diff($value, $array2[$key]);
					if (0 === count($diff)) {
						unset($array1[$key]);
					} else {
						$array1[$key] = $diff;
					}
				} elseif ($value == $array2[$key]) {
					unset($array1[$key]);
				}
				unset($array2[$key]);
			}
		}
		foreach ($array2 as $key => $value) {
			if (FALSE === isset($array1[$key])) {
				$array1[$key] = $value;
			}
		}
		return $array1;
	}

	/**
	 * @param array $firstArray First array
	 * @param array $secondArray Second array, overruling the first array
	 * @param boolean $notAddKeys If set, keys that are NOT found in $firstArray will not be set. Thus only existing value can/will be overruled from second array.
	 * @param boolean $includeEmptyValues If set, values from $secondArray will overrule if they are empty or zero. Default: TRUE
	 * @param boolean $enableUnsetFeature If set, special values "__UNSET" can be used in the second array in order to unset array keys in the resulting array.
	 * @return array Resulting array where $secondArray values has overruled $firstArray values
	 */
	static public function mergeRecursiveOverrule(array $firstArray, array $secondArray, $notAddKeys = FALSE, $includeEmptyValues = TRUE, $enableUnsetFeature = TRUE) {
		ArrayUtility::mergeRecursiveWithOverrule($firstArray, $secondArray, !$notAddKeys, $includeEmptyValues, $enableUnsetFeature);
		return $firstArray;
	}

}
