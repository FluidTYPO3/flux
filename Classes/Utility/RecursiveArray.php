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
 * ClipBoard Utility
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_RecursiveArray {

	/**
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function merge($array1, $array2) {
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
	 * Parses a flexForm node recursively and takes care of sections etc
	 *
	 * @param array $nodeArray The flexForm node to parse
	 * @param string $valuePointer The valuePointer to use for value retrieval
	 * @return array
	 */
	public static function walkFlexFormNode($nodeArray, $valuePointer = 'vDEF') {
		if (FALSE === is_array($nodeArray)) {
			return $nodeArray;
		}
		$return = array();
		foreach ($nodeArray as $nodeKey => $nodeValue) {
			if ($nodeKey === $valuePointer) {
				return $nodeValue;
			}
			if (in_array($nodeKey, array('el', '_arrayContainer'))) {
				return self::walkFlexFormNode($nodeValue, $valuePointer);
			}
			if (substr($nodeKey, 0, 1) === '_') {
				continue;
			}
			if (strpos($nodeKey, '.')) {
				$nodeKeyParts = explode('.', $nodeKey);
				$currentNode = &$return;
				$total = (count($nodeKeyParts) - 1);
				for ($i = 0; $i < $total; $i++) {
					$currentNode = &$currentNode[$nodeKeyParts[$i]];
				}
				$newNode = array(next($nodeKeyParts) => $nodeValue);
				$currentNode = self::walkFlexFormNode($newNode, $valuePointer);
			} else if (is_array($nodeValue)) {
				if (array_key_exists($valuePointer, $nodeValue)) {
					$return[$nodeKey] = $nodeValue[$valuePointer];
				} else {
					$return[$nodeKey] = self::walkFlexFormNode($nodeValue, $valuePointer);
				}
			} else {
				$return[$nodeKey] = $nodeValue;
			}
		}
		return $return;
	}

}
