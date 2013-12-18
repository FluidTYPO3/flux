<?php
namespace FluidTYPO3\Flux\Service;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@namelesscoder.net>
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

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class LanguageFileServiceTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function canDispatchMessage() {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
		$this->callInaccessibleMethod($instance, 'message', 'Test');
	}

	/**
	 * @test
	 */
	public function performsEarlyReturnOnUnsupportedFileExtension() {
		$return = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService')->writeLanguageLabel('/dev/null', 'void', 'void');
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function performsEarlyReturnOnInvalidId() {
		$return = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService')->writeLanguageLabel('/dev/null', 'void', 'this-is-an-invalid-id');
		$this->assertEmpty($return);
	}

	/**
	 * @test
	 */
	public function kickstartsXlfFileIfDoesNotExist() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$configurationService = $this->createFluxServiceInstance();
		$domDocument = new \DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$languageKeys = array('default');
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('buildSourceForXlfFile', 'prepareDomDocument', 'getLanguageKeys'));
		$instance->expects($this->atLeastOnce())->method('getLanguageKeys')->will($this->returnValue($languageKeys));
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXlfFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
		$instance->writeLanguageLabel($dummyFile, 'test', 'test');
	}

	/**
	 * @test
	 */
	public function canCreateXlfLanguageNode() {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
		$domDocument = new \DOMDocument();
		$parent = $domDocument->createElement('parent');
		$this->callInaccessibleMethod($instance, 'createXmlLanguageNode', $domDocument, $parent, 'test');
		$this->assertNotEmpty($parent->getElementsByTagName('languageKey')->item(0));
	}

	/**
	 * @test
	 */
	public function canBuildXlfFileSource() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$languageKeys = array('default');
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('prepareDomDocument', 'createXlfLanguageNode', 'getLanguageKeys'));
		$instance->expects($this->atLeastOnce())->method('getLanguageKeys')->will($this->returnValue($languageKeys));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->once())->method('createXlfLanguageNode');
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXlfFile($fileName, 'test');
		$this->assertEquals($domDocument->saveXML(), $result);
	}

	/**
	 * @test
	 */
	public function canBuildXlfFileSourceButReturnsTrueIfFileAndNodeExists() {
		$dummyFile = 'typo3temp/lang.xlf';
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		$body = $domDocument->createElement('body');
		$node = $domDocument->createElement('file');
		$domDocument->appendChild($node);
		$node->appendChild($body);
		$transUnit = $domDocument->createElement('trans-unit', 'test');
		$transUnit->setAttribute('id', 'test');
		$body->appendChild($transUnit);
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('prepareDomDocument', 'createXlfLanguageNode'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->never())->method('createXlfLanguageNode');
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
		$result = $instance->buildSourceForXlfFile($fileName, 'test');
		$this->assertTrue($result);
	}

	/**
	 * @test
	 */
	public function kickstartsXmlFileIfDoesNotExist() {
		$dummyFile = 'typo3temp/lang.xml';
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		$meta = $domDocument->createElement('meta');
		$description = $domDocument->createElement('description');
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label');
		$label->setAttribute('id', 'void');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$meta->appendChild($description);
		$domDocument->appendChild($node);
		$domDocument->appendChild($meta);
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('buildSourceForXmlFile', 'prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXmlFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
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
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		GeneralUtility::writeFile($fileName, $domDocument->saveXML());
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('buildSourceForXmlFile', 'prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('buildSourceForXmlFile')->with($fileName, 'test')->will($this->returnValue($domDocument->saveXML()));
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
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
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label', 'test');
		$label->setAttribute('index', 'void');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$domDocument->appendChild($node);
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('prepareDomDocument', 'writeFile'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$instance->expects($this->any())->method('writeFile')->will($this->returnValue(TRUE));
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
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
		$fileName = GeneralUtility::getFileAbsFileName($dummyFile);
		if (TRUE === file_exists($fileName)) {
			unlink($fileName);
		}
		$domDocument = new \DOMDocument();
		$node = $domDocument->createElement('data');
		$languageKey = $domDocument->createElement('languageKey');
		$label = $domDocument->createElement('label', 'test');
		$label->setAttribute('index', 'test');
		$languageKey->appendChild($label);
		$node->appendChild($languageKey);
		$domDocument->appendChild($node);
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('prepareDomDocument'));
		$instance->expects($this->atLeastOnce())->method('prepareDomDocument')->with($fileName)->will($this->returnValue($domDocument));
		$configurationService = $this->createFluxServiceInstance();
		ObjectAccess::setProperty($instance, 'configurationService', $configurationService, TRUE);
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
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
		$domDocument = new \DOMDocument();
		$parent = $domDocument->createElement('parent');
		$this->callInaccessibleMethod($instance, 'createXlfLanguageNode', $domDocument, $parent, 'test');
		$this->assertNotEmpty($parent->getElementsByTagName('trans-unit')->item(0));
	}

	/**
	 * @test
	 */
	public function performReset() {
		$this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService')->reset();
	}

	/**
	 * @test
	 */
	public function canWriteFile() {
		$tempFile = GeneralUtility::tempnam('test');
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
		$this->callInaccessibleMethod($instance, 'writeFile', $tempFile, 'test');
		$this->assertFileExists($tempFile);
	}

	/**
	 * @test
	 */
	public function canReadFile() {
		$tempFile = GeneralUtility::tempnam('test');
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
		$this->callInaccessibleMethod($instance, 'writeFile', $tempFile, 'test');
		$result = $this->callInaccessibleMethod($instance, 'readFile', $tempFile);
		$this->assertEquals('test', $result);
		unlink($tempFile);
	}

	/**
	 * @test
	 */
	public function canLoadLanguageRecordsFromDatabase() {
		$instance = $this->objectManager->get('FluidTYPO3\Flux\Service\LanguageFileService');
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
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('loadLanguageRecordsFromDatabase'));
		$instance->expects($this->once())->method('loadLanguageRecordsFromDatabase')->will($this->returnValue($languageRecords));
		$result = $this->callInaccessibleMethod($instance, 'getLanguageKeys');
		$this->assertIsArray($result);
		$this->assertEquals(array('default', 'en'), $result);
	}

	/**
	 * @test
	 */
	public function canPrepareDomDocument() {
		$fileName = GeneralUtility::tempnam('test');
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService', array('readFile'));
		$source = '<test></test>';
		$domDocument = new \DOMDocument();
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
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService');
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
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService');
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
		$instance = $this->getMock('FluidTYPO3\Flux\Service\LanguageFileService');
		$filename = '/tmp/lang.xlf';
		$expected = '/tmp/da.lang.xlf';
		$language = 'da';
		$result = $this->callInaccessibleMethod($instance, 'localizeXlfFilePathAndFilename', $filename, $language);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @test
	 */
	public function writeFileCreatesDirectoryIfMissing() {
		$instance = $this->createInstance();
		$directory = GeneralUtility::getFileAbsFileName('typo3temp/' . rand(100000, 999999));
		$filepath = $directory  . '/file.xlf';
		$this->assertFileNotExists($directory);
		$this->callInaccessibleMethod($instance, 'writeFile', $filepath, 'test');
		$this->assertFileExists($directory);
		$this->assertFileExists($filepath);
		unlink($filepath);
		rmdir($directory);
	}

}
