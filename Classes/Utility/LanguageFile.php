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
 * Language File Utility
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Utility
 */
class Tx_Flux_Utility_LanguageFile {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected static $service = NULL;

	/**
	 * @param string $file
	 * @param string $identifier
	 * @param string $id
	 */
	public static function autoWriteLanguageLabel($file, $identifier, $id) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['rewriteLanguageFiles']) {
			return;
		}
		self::message('Generated automatic LLL path for entity called "' . $identifier . '" in file "' . $file . '"');
		$allowed = 'a-z\.';
		$pattern = '/[^' . $allowed . ']+/i';
		if (preg_match($pattern, $id) || preg_match($pattern, $identifier)) {
			self::message('Cowardly refusing to create an invalid LLL reference called "' . $identifier . '" ' .
				' in a Flux form called "' . $id . '" - one or both contains invalid characters. Allowed: dots and "' .
				$allowed . '".');
			return;
		}
		$file = substr($file, 4);
		$filePathAndFilename = t3lib_div::getFileAbsFileName($file);
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->preserveWhiteSpace = FALSE;
		$dom->load($filePathAndFilename);
		$dom->formatOutput = TRUE;
		foreach ($dom->getElementsByTagName('languageKey') as $languageNode) {
			$nodes = array();
			foreach ($languageNode->getElementsByTagName('label') as $labelNode) {
				$key = (string) $labelNode->attributes->getNamedItem('index')->firstChild->textContent;
				if ($key === $identifier) {
					self::message('Skipping LLL file merge for label "' . $identifier.
						'"; it already exists in file "' . $filePathAndFilename . '"');
					return;
				}
				$nodes[$key] = $labelNode;
			}
			$node = $dom->createElement('label', $identifier);
			$attribute = $dom->createAttribute('index');
			$attribute->appendChild($dom->createTextNode($identifier));
			$node->appendChild($attribute);
			$nodes[$identifier] = $node;
			ksort($nodes);
			foreach ($nodes as $labelNode) {
				$languageNode->appendChild($labelNode);
			}
		}
		$xml = $dom->saveXML();
		if (FALSE === $xml) {
			self::message('Skipping LLL file saving due to an error while generating the XML.',
				t3lib_div::SYSLOG_SEVERITY_FATAL);
		} else {
			self::message('Rewrote "' . $file . '" by adding placeholder label for "' . $identifier . '"');
			file_put_contents($filePathAndFilename, $xml);
		}
	}

	/**
	 * @return Tx_Flux_Service_FluxService
	 */
	protected static function getServiceInstance() {
		if (NULL === self::$service) {
			$objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
			self::$service = $objectManager->get('Tx_Flux_Service_FluxService');
		}
		return self::$service;
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @return void
	 */
	protected static function message($message, $severity = t3lib_div::SYSLOG_SEVERITY_INFO) {
		self::getServiceInstance()->message($message, $severity, 'Flux Language File Utility');
	}

}
