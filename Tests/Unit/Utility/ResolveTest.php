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
class Tx_Flux_Utility_ResolveTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @test
	 */
	public function returnsClassIfClassExists() {
		$className = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Content');
		$instance = $this->objectManager->get($className);
		$this->assertInstanceOf('Tx_Flux_Controller_AbstractFluxController', $instance);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerClassNameDoesNotExist() {
		$result = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerActionDoesNotExist() {
		$result = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'void', 'Content');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForClassIfSetToHardFail() {
		$this->setExpectedException('RuntimeException', NULL, 1364498093);
		Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'render', 'Void', TRUE);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForActionIfSetToHardFail() {
		$this->setExpectedException('RuntimeException', NULL, 1364498223);
		Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('flux', 'void', 'Content', FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassExists() {
		class_alias('Tx_Flux_Controller_AbstractFluxController', 'Void\\NoName\\Controller\\FakeController');
		$result = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Fake');
		$this->assertSame('Void\\NoName\\Controller\\FakeController', $result);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassDoesNotExist() {
		$result = Tx_Flux_Utility_Resolve::resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectRequestArgumentsBasedOnPluginSignature() {
		$result = Tx_Flux_Utility_Resolve::resolveOverriddenFluxControllerActionNameFromRequestParameters('tx_void_fake');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectWidgetTemplatePathAndFilenameAndTrimsTrailingSlash() {
		$templateRootPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/');
		$expectedDefault = $templateRootPath . 'ViewHelpers/Widget/Grid/Index.html';
		$expectedLegacy = $templateRootPath . 'ViewHelpers/Widget/Grid/Legacy.html';
		$expectedWithGridelementsVersionTwo = $templateRootPath . 'ViewHelpers/Widget/Grid/GridElements.html';
		$utility = new Tx_Flux_Utility_Resolve();
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'initialized', TRUE, TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'isLegacyCoreVersion', FALSE, TRUE);
		$this->assertSame($expectedDefault, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', TRUE, TRUE);
		$this->assertSame($expectedWithGridelementsVersionTwo, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', FALSE, TRUE);
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'isLegacyCoreVersion', TRUE, TRUE);
		$this->assertSame($expectedLegacy, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		\TYPO3\CMS\Extbase\Reflection\ObjectAccess::setProperty($utility, 'initialized', FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canDetectCurrentPageRecord() {
		$result = Tx_Flux_Utility_Resolve::resolveCurrentPageRecord();
		$this->assertNull($result);
		$expected = array('uid' => 99999999);
		$GLOBALS['TSFE'] = new tslib_fe($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
		$GLOBALS['TSFE']->page = $expected;
		$result = Tx_Flux_Utility_Resolve::resolveCurrentPageRecord();
		$this->assertSame($result, $expected);
	}

}
