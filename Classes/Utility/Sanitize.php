<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
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
 * Sanitize Utility
 *
 * @author Danilo Bürger <danilo.buerger@hmspl.de>, Heimspiel GmbH
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_Sanitize {

	const INPUT_GET = 'get';
	const INPUT_POST = 'post';
	const INPUT_GP = 'gp';

	/**
	 * @param string $type
	 * @param string $key
	 * @param int $filter
	 * @throws RuntimeException
	 * @return string
	 */
	public static function filter($type, $key, $filter) {
		$keyParts = explode('.', $key);
		$firstKeyPart = array_shift($keyParts);

		if (self::INPUT_GET === $type) {
			$data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET($firstKeyPart);
		} elseif (self::INPUT_POST === $type) {
			$data = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST($firstKeyPart);
		} elseif (self::INPUT_GP === $type) {
			$data = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($firstKeyPart);
		} else {
			throw new RuntimeException('Input type was invalid.', 1386259875);
		}

		foreach ($keyParts as $keyPart) {
			if (TRUE === isset($data[$keyPart])) {
				$data = $data[$keyPart];
			} else {
				$data = NULL;
				break;
			}
		}

		return filter_var($data, $filter);
	}

	/**
	 * @param string $key
	 * @param int $filter
	 * @return string
	 */
	public static function get($key, $filter) {
		return self::filter(self::INPUT_GET, $key, $filter);
	}

	/**
	 * @param string $key
	 * @param int $filter
	 * @return string
	 */
	public static function post($key, $filter) {
		return self::filter(self::INPUT_POST, $key, $filter);
	}

	/**
	 * @param string $key
	 * @param int $filter
	 * @return string
	 */
	public static function gp($key, $filter) {
		return self::filter(self::INPUT_GP, $key, $filter);
	}

}
