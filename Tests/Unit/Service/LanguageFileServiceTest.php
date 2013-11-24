<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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
 * ************************************************************* */

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Service_LanguageFileServiceTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canDispatchMessage() {
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$this->callInaccessibleMethod($instance, 'message', 'Test');
	}

	/**
	 * @test
	 */
	public function performsEarlyReturnOnUnsupportedFileExtension() {
		$return = $this->objectManager->get('Tx_Flux_Service_LanguageFileService')->writeLanguageLabel('/dev/null', 'void', 'void');
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function performsEarlyReturnOnInvalidId() {
		$return = $this->objectManager->get('Tx_Flux_Service_LanguageFileService')->writeLanguageLabel('/dev/null', 'void', 'this-is-an-invalid-id');
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function kickstartsXlfFileIfDoesNotExist() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$configurationService = $this->createFluxServiceInstance();
		$domDocument = new DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('buildSourceForXlfFile', 'prepareDomDocument'));
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXlfFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
	}

	/**
	 * @test
	 */
	public function canCreateXlfLanguageNode() {
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$domDocument = new DOMDocument();
		$parent = $domDocument->createElement('parent');
		$this->callInaccessibleMethod($instance, 'createXmlLanguageNode', $domDocument, $parent, 'test');
		$this->assertNotEmpty($parent->getElementsByTagName('languageKey')->item(0));
	}

	/**
	 * @test
	 */
	public function canBuildXlfFileSource() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('prepareDomDocument', 'createXlfLanguageNode'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->once())->method('createXlfLanguageNode');
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXlfFile($fileName, 'test');
		$this->assertEquals($domDocument->saveXML(), $result);
	}

	/**
	 * @test
	 */
	public function canBuildXlfFileSourceButReturnsTrueIfFileAndNodeExists() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$transUnit = $domDocument->createElement('trans-unit', 'test');
		$transUnit->setAttribute('id', 'test');
		$body->appendChild($transUnit);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('prepareDomDocument', 'createXlfLanguageNode'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->never())->method('createXlfLanguageNode');
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXlfFile($fileName, 'test');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function kickstartsXmlFileIfDoesNotExist() {
		$dummyFile = 'typo3temp/lang.xml';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label');
		$label->setAttribute('id', 'void');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$domDocument->appendChild($node);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('buildSourceForXmlFile', 'prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXmlFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
	}

	/**
	 * @test
	 */
	public function kickstartXmlFileReturnsFalseIfExistingFileHasNoDataNode() {
		$dummyFile = 'typo3temp/lang.xml';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		t3lib_div::writeFile($fileName, $domDocument->saveXML());
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('buildSourceForXmlFile', 'prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXmlFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $this->callInaccessibleMethod($instance, 'kickstartXmlFile', $fileName);
		$this->assertFalse($result);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
	}

	/**
	 * @test
	 */
	public function canBuildXmlFileSource() {
		$dummyFile = 'typo3temp/lang.xml';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label', 'test');
		$label->setAttribute('index', 'void');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$domDocument->appendChild($node);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('prepareDomDocument', 'writeFile'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('writeFile')->will($this->returnValue(TRUE));
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXmlFile($fileName, 'test');
		$this->assertEquals($domDocument->saveXML(), $result);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
	}

	/**
	 * @test
	 */
	public function canBuildXmlFileSourceButReturnsTrueIfFileAndNodeExists() {
		$dummyFile = 'typo3temp/lang.xml';
		$fileName = t3lib_div::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new DOMDocument();
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label', 'test');
		$label->setAttribute('index', 'test');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$domDocument->appendChild($node);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$configurationService = $this->createFluxServiceInstance();
		Tx_Extbase_Reflection_ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXmlFile($fileName, 'test');
		$this->assertTrue($result);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
	}

	/**
	 * @test
	 */
	public function canCreateXmlLanguageNode() {
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$domDocument = new DOMDocument();
		$parent = $domDocument->createElement('parent');
		$this->callInaccessibleMethod($instance, 'createXlfLanguageNode', $domDocument, $parent, 'test');
		$this->assertNotEmpty($parent->getElementsByTagName('trans-unit')->item(0));
	}

	/**
	 * @test
	 */
	public function performReset() {
		$this->objectManager->get('Tx_Flux_Service_LanguageFileService')->reset();
	}

	/**
	 * @test
	 */
	public function canWriteFile() {
		$tempFile = t3lib_div::tempnam('test');
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$this->callInaccessibleMethod($instance, 'writeFile', $tempFile, 'test');
		$this->assertFileExists($tempFile);
	}

	/**
	 * @test
	 */
	public function canReadFile() {
		$tempFile = t3lib_div::tempnam('test');
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$this->callInaccessibleMethod($instance, 'writeFile', $tempFile, 'test');
		$result = $this->callInaccessibleMethod($instance, 'readFile', $tempFile);
		$this->assertEquals('test', $result);
		unlink($tempFile);
	}

	/**
	 * @test
	 */
	public function canLoadLanguageRecordsFromDatabase() {
		$instance = $this->objectManager->get('Tx_Flux_Service_LanguageFileService');
		$result = $this->callInaccessibleMethod($instance, 'loadLanguageRecordsFromDatabase');
		$this->assertIsArray($result);
	}

	/**
	 * @test
	 */
	public function canGetLanguageKeys() {
		$languageRecords = array(
			array('flag' => 'en')
		);
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('loadLanguageRecordsFromDatabase'));
		$instance->expects($this->once())->method('loadLanguageRecordsFromDatabase')->will($this->returnValue($languageRecords));
		$result = $this->callInaccessibleMethod($instance, 'getLanguageKeys');
		$this->assertIsArray($result);
		$this->assertEquals(array('default', 'en'), $result);
	}

	/**
	 * @test
	 */
	public function canPrepareDomDocument() {
		$fileName = t3lib_div::tempnam('test');
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService', array('readFile'));
		$source = '<test></test>';
		$domDocument = new DOMDocument();
		$domDocument->appendChild($domDocument->createElement('test'));
		$instance->expects($this->once())->method('readFile')->with($fileName)->will($this->returnValue($source));
		$this->callInaccessibleMethod($instance, 'prepareDomDocument', $fileName);
		$result = $this->callInaccessibleMethod($instance, 'prepareDomDocument', $fileName);
		$this->assertEquals($domDocument, $result);
	}

	/**
	 * @test
	 */
	public function sanitizeFilenameAddsValidExtensionIfCurrentExtensionIsInvalid() {
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService');
		$filename = '/tmp/bad.json';
		$expected = '/tmp/bad.json.xml';
		$extension = 'xml';
		$result = $this->callInaccessibleMethod($instance, 'sanitizeFilePathAndFilename', $filename, $extension);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function localizeXlfFilePathAndFilenameReturnsRootFileIfLanguageIsDefault() {
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService');
		$filename = '/tmp/lang.xlf';
		$expected = '/tmp/lang.xlf';
		$language = 'default';
		$result = $this->callInaccessibleMethod($instance, 'localizeXlfFilePathAndFilename', $filename, $language);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function localizeXlfFilePathAndFilenameAddsLanguageIfNotDefault() {
		$instance = $this->getMock('Tx_Flux_Service_LanguageFileService');
		$filename = '/tmp/lang.xlf';
		$expected = '/tmp/da.lang.xlf';
		$language = 'da';
		$result = $this->callInaccessibleMethod($instance, 'localizeXlfFilePathAndFilename', $filename, $language);
		$this->assertEquals($expected, $result);
	}

}
