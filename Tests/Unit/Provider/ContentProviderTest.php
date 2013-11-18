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
class Tx_Flux_Provider_ContentProviderTest extends Tx_Flux_Provider_AbstractProviderTest {

	/**
	 * @test
	 */
	public function prunesEmptyFieldNodesOnRecordSave() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = Tx_Flux_Tests_Fixtures_Data_Xml::EXPECTING_FLUX_PRUNING;
		$provider = $this->getConfigurationProviderInstance();
		$tceMain = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$provider->postProcessRecord('update', $row['uid'], $row, $tceMain);
		$this->assertNotContains('<field index=""></field>', $row['pi_flexform']);
	}

	/**
	 * @test
	 */
	public function triggersContentManipulatorOnDatabaseOperationNew() {
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$tceMain = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$provider->postProcessDatabaseOperation('new', $row['uid'], $row, $tceMain);
	}

	/**
	 * @test
	 */
	public function triggersContentManipulatorOnPasteCommandWithCallbackInUrl() {
		$_GET['CB'] = array('paste' => 'tt_content|0');
		$row = Tx_Flux_Tests_Fixtures_Data_Records::$contentRecordWithoutParentAndWithoutChildren;
		$provider = $this->getConfigurationProviderInstance();
		$tceMain = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
		$relativeUid = 0;
		$provider->postProcessCommand('move', 0, $row, $relativeUid, $tceMain);
	}

	/**
	 * @test
	 */
	public function canGetExtensionKey() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$extensionKey = $provider->getExtensionKey($record);
		$this->assertSame('flux', $extensionKey);
	}

	/**
	 * @test
	 */
	public function canGetTableName() {
		$provider = $this->getConfigurationProviderInstance();
		$record = $this->getBasicRecord();
		$tableName = $provider->getTableName($record);
		$this->assertSame('tt_content', $tableName);
	}

}
