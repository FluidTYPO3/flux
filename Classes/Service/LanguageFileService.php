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
 * Language File Service
 *
 * @author Claus Due, Wildside A/S
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_LanguageFileService {

	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected $configurationService;
	/**
	 * @var Tx_Flux_Service_FluxService
	 */
	protected static $service = NULL;

	/**
	 * @var array
	 */
	protected static $validExtensions = array('xml', 'xlf');

	/**
	 * @var array
	 */
	protected static $documents = array();

	const TEMPLATE_XML = <<< XML
<T3locallang>
	<meta type="array">
		<type>module</type>
		<description></description>
	</meta>
	<data type="array"></data>
</T3locallang>
XML;

	const TEMPLATE_XLF = <<< XML
<xliff version="1.0">
	<file source-language="en" datatype="plaintext" original="messages" date="" product-name="">
		<header/>
		<body></body>
	</file>
</xliff>
XML;

	/**
	 * @param Tx_Flux_Service_FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(Tx_Flux_Service_FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @return void
	 */
	public function reset() {
		self::$documents = array();
	}

	/**
	 * @param string $file
	 * @param string $identifier
	 * @param string $id
	 * @return NULL
	 */
	public function writeLanguageLabel($file, $identifier, $id) {
		$pattern = '/[^a-z0-9_]+/i';
		$patternIdentifier = '/[^a-z0-9\._]+/i';
		if (preg_match($pattern, $id) || preg_match($patternIdentifier, $identifier)) {
			$this->message('Cowardly refusing to create an invalid LLL reference called "' . $identifier . '" ' .
				' in a Flux form called "' . $id . '" - one or both contains invalid characters.');
			return NULL;
		}
		$file = 0 === strpos($file, 'LLL:') ? substr($file, 4) : $file;
		$filePathAndFilename = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($file);
		$extension = pathinfo($filePathAndFilename, PATHINFO_EXTENSION);
		if (FALSE === in_array($extension, self::$validExtensions)) {
			return NULL;
		}
		$buildMethodName = 'buildSourceFor' . ucfirst($extension) . 'File';
		$kickstartMethodName = 'kickstart' . ucfirst($extension) . 'File';
		$languages = $this->getLanguageKeys();
		call_user_func_array(array(self, $kickstartMethodName), array($filePathAndFilename, $languages));
		$source = call_user_func_array(array(self, $buildMethodName), array($filePathAndFilename, $identifier));
		if (TRUE === $source) {
			$this->message('Wrote "LLL:' . $file . ':' . $identifier . '" - or label already exists');
		} elseif (TRUE === is_string($source)) {
			if (TRUE === $this->writeFile($filePathAndFilename, $source)) {
				$this->message('Wrote "LLL:' . $file . ':' . $identifier . '"');
			}
		}
	}

	/**
	 * @param string $filePathAndFilename
	 * @param string $identifier
	 * @return string|boolean
	 */
	public function buildSourceForXmlFile($filePathAndFilename, $identifier) {
		$filePathAndFilename = $this->sanitizeFilePathAndFilename($filePathAndFilename, 'xml');
		$dom = $this->prepareDomDocument($filePathAndFilename);
		foreach ($dom->getElementsByTagName('languageKey') as $languageNode) {
			$nodes = array();
			foreach ($languageNode->getElementsByTagName('label') as $labelNode) {
				$key = (string) $labelNode->attributes->getNamedItem('index')->firstChild->textContent;
				if ($key === $identifier) {
					return TRUE;
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
		return $xml;
	}

	/**
	 * @param string $filePathAndFilename
	 * @param array $languages
	 * @return boolean
	 */
	public function kickstartXmlFile($filePathAndFilename, $languages = array('default')) {
		$filePathAndFilename = $this->sanitizeFilePathAndFilename($filePathAndFilename, 'xml');
		if (TRUE === file_exists($filePathAndFilename)) {
			$dom = $this->prepareDomDocument($filePathAndFilename);
		} else {
			$dom = new DOMDocument();
			$dom->loadXML(self::TEMPLATE_XML);
		}
		$dataNode = $dom->getElementsByTagName('data')->item(0);
		if (NULL === $dataNode) {
			return FALSE;
		}
		$dom->getElementsByTagName('description')->item(0)->nodeValue = 'Labels for languages: ' . implode(', ', $languages);
		$missingLanguages = $languages;
		if (0 < $dataNode->childNodes->length) {
			$missingLanguages = $languages;
			foreach ($dom->getElementsByTagName('languageKey') as $languageNode) {
				$languageKey = $languageNode->getAttribute('index');
				if (TRUE === in_array($languageKey, $missingLanguages)) {
					unset($missingLanguages[array_search($languageKey, $missingLanguages)]);
				}
			}
		}
		foreach ($missingLanguages as $missingLanguageKey) {
			$this->createXmlLanguageNode($dom, $dataNode, $missingLanguageKey);
		}
		self::$documents[$filePathAndFilename] = $dom;
		$this->writeFile($filePathAndFilename, $dom->saveXML());
		return file_exists($filePathAndFilename);
	}

	/**
	 * @param DomDocument $dom
	 * @param DomNode $parent
	 * @param string $languageKey
	 * @return void
	 */
	protected function createXmlLanguageNode(DomDocument $dom, DomNode $parent, $languageKey) {
		$languageNode = $dom->createElement('languageKey');
		$indexAttribute = $dom->createAttribute('index');
		$indexAttribute->nodeValue = $languageKey;
		$typeAttribute = $dom->createAttribute('type');
		$typeAttribute->nodeValue = 'array';
		$languageNode->appendChild($indexAttribute);
		$languageNode->appendChild($typeAttribute);
		$parent->appendChild($languageNode);
	}

	/**
	 * @param string $filePathAndFilename
	 * @param string $identifier
	 * @return string|boolean
	 */
	public function buildSourceForXlfFile($filePathAndFilename, $identifier) {
		$filePathAndFilename = $this->sanitizeFilePathAndFilename($filePathAndFilename, 'xlf');
		$languages = $this->getLanguageKeys();
		foreach ($languages as $language) {
			$translationPathAndFilename = $this->localizeXlfFilePathAndFilename($filePathAndFilename, $language);
			$dom = $this->prepareDomDocument($translationPathAndFilename);
			$dateNode = $dom->createAttribute('date');
			$dateNode->nodeValue = date('c');
			$dom->getElementsByTagName('file')->item(0)->appendChild($dateNode);
			$body = $dom->getElementsByTagName('body')->item(0);
			foreach ($dom->getElementsByTagName('trans-unit') as $node) {
				if ($node->getAttribute('id') === $identifier) {
					return TRUE;
				}
			}
			$this->createXlfLanguageNode($dom, $body, $identifier);
			$xml = $dom->saveXML();
			self::$documents[$translationPathAndFilename] = $dom;
		}
		return $xml;
	}

	/**
	 * @param DomDocument $dom
	 * @param DomNode $parent
	 * @param string $identifier
	 * @return void
	 */
	protected function createXlfLanguageNode(DomDocument $dom, DomNode $parent, $identifier) {
		$labelNode = $dom->createElement('trans-unit');
		$idAttribute = $dom->createAttribute('id');
		$idAttribute->nodeValue = $identifier;
		$spaceAttribute = $dom->createAttribute('xml:space');
		$spaceAttribute->nodeValue = 'preserve';
		$sourceNode = $dom->createElement('source');
		$sourceNode->nodeValue = $identifier;
		$labelNode->appendChild($idAttribute);
		$labelNode->appendChild($spaceAttribute);
		$labelNode->appendChild($sourceNode);
		$parent->appendChild($labelNode);
	}

	/**
	 * @param string $filePathAndFilename
	 * @param array $languageOrLanguages
	 * @return boolean|array
	 */
	public function kickstartXlfFile($filePathAndFilename, $languageOrLanguages = array('default')) {
		if (TRUE === is_array($languageOrLanguages)) {
			$results = array();
			foreach ($languageOrLanguages as $language) {
				$results[$language] = $this->kickstartXlfFile($filePathAndFilename, $language);
			}
			return $results;
		}
		$filePathAndFilename = $this->sanitizeFilePathAndFilename($filePathAndFilename, 'xlf');
		$filePathAndFilename = $this->localizeXlfFilePathAndFilename($filePathAndFilename, $languageOrLanguages);
		if (FALSE === file_exists($filePathAndFilename)) {
			$this->writeFile($filePathAndFilename, self::TEMPLATE_XLF);
		}
		if (TRUE === isset(self::$documents[$filePathAndFilename])) {
			return self::$documents[$filePathAndFilename];
		}
		$truncated = substr($filePathAndFilename, strlen(PATH_site) + 1);
		$truncatedParts = explode('/', $truncated);
		$dom = $this->prepareDomDocument($filePathAndFilename);
		$fileNode = $dom->getElementsByTagName('file')->item(0);
		$productNode = $dom->createAttribute('product-name');
		$productNode->nodeValue = $truncatedParts[2];
		$fileNode->appendChild($productNode);
		self::$documents[$filePathAndFilename] = $dom;
		return file_exists($filePathAndFilename);
	}

	/**
	 * @param string $filePathAndFilename
	 * @param string $language
	 * @return mixed
	 */
	protected function localizeXlfFilePathAndFilename($filePathAndFilename, $language) {
		$basename = pathinfo($filePathAndFilename, PATHINFO_FILENAME);
		if ('default' !== $language) {
			$filePathAndFilename = str_replace($basename, $language . '.' . $basename, $filePathAndFilename);
		}
		return $filePathAndFilename;
	}

	/**
	 * @param string $filePathAndFilename
	 * @param string $extension
	 * @return string
	 */
	protected function sanitizeFilePathAndFilename($filePathAndFilename, $extension) {
		$detectedExtension = pathinfo($filePathAndFilename, PATHINFO_EXTENSION);
		if ($extension !== $detectedExtension) {
			$filePathAndFilename .= '.' . $extension;
		}
		return $filePathAndFilename;
	}

	/**
	 * @param $filePathAndFilename
	 * @return DomDocument|FALSE
	 */
	protected function prepareDomDocument($filePathAndFilename) {
		if (TRUE === isset(self::$documents[$filePathAndFilename])) {
			return self::$documents[$filePathAndFilename];
		}
		$contents = $this->readFile($filePathAndFilename);
		$dom = new DOMDocument('1.0', 'utf-8');
		$dom->preserveWhiteSpace = FALSE;
		$dom->formatOutput = TRUE;
		$dom->loadXML($contents);
		self::$documents[$filePathAndFilename] = $dom;
		return $dom;
	}

	/**
	 * @return array
	 */
	protected function getLanguageKeys() {
		$sysLanguages = $this->loadLanguageRecordsFromDatabase();
		$languageKeys = array('default');
		foreach ($sysLanguages as $language) {
			array_push($languageKeys, $language['flag']);
		}
		return array_unique($languageKeys);
	}

	/**
	 * @return array
	 */
	protected function loadLanguageRecordsFromDatabase() {
		$cObj = new tslib_cObj();
		$GLOBALS['TSFE'] = new tslib_fe($GLOBALS['TYPO3_CONF_VARS'], 0, 0);
		$GLOBALS['TSFE']->sys_page = new t3lib_pageSelect();
		$select = 'flag';
		$from = 'sys_language';
		$where = '1=1' . $cObj->enableFields('sys_language');
		$sysLanguages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select, $from, $where);
		return (array) $sysLanguages;
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @return void
	 */
	protected function message($message, $severity = \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_INFO) {
		if (TRUE === isset($GLOBALS['BE_USER'])) {
			$this->configurationService->message($message, $severity, 'Flux Language File Utility');
		}
	}

	/**
	 * @param string $filePathAndFilename
	 * @param string $content
	 * @return boolean
	 */
	protected function writeFile($filePathAndFilename, $content) {
		return \TYPO3\CMS\Core\Utility\GeneralUtility::writeFile($filePathAndFilename, $content);
	}

	/**
	 * @param string $filePathAndFilename
	 * @return string
	 */
	protected function readFile($filePathAndFilename) {
		return file_get_contents($filePathAndFilename);
	}

}
