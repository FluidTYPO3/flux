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
class Tx_Flux_Backend_TypoScriptTemplateTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function canPreProcessIncludeStaticTypoScriptResources() {
		Tx_Flux_Core::addGlobalTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
		$function = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'];
		$template = $this->objectManager->get('t3lib_TStemplate');
		$parameters = array(
			'row' => Tx_Flux_Tests_Fixtures_Data_Records::$sysTemplateRoot
		);
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($function, $parameters, $template);
		$this->assertContains(self::FIXTURE_TYPOSCRIPT_DIR, $parameters['row']['include_static_file']);
	}

	/**
	 * @test
	 */
	public function leavesRecordsWhichAreNotRootsUntouched() {
		Tx_Flux_Core::addGlobalTypoScript(self::FIXTURE_TYPOSCRIPT_DIR);
		$function = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSources']['flux'];
		$template = $this->objectManager->get('t3lib_TStemplate');
		$parameters = array(
			'row' => Tx_Flux_Tests_Fixtures_Data_Records::$sysTemplateRoot
		);
		$parameters['row']['root'] = 0;
		\TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($function, $parameters, $template);
		$this->assertNotContains(self::FIXTURE_TYPOSCRIPT_DIR, $parameters['row']['include_static_file']);
		$this->assertSame(Tx_Flux_Tests_Fixtures_Data_Records::$sysTemplateRoot['include_static_file'], $parameters['row']['include_static_file']);
	}

}
