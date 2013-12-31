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
 * ************************************************************* */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Flux
 */
class ResolveTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function returnsClassIfClassExists() {
		$className = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Content');
		$instance = $this->objectManager->get($className);
		$this->assertInstanceOf('FluidTYPO3\Flux\Controller\AbstractFluxController', $instance);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerClassNameDoesNotExist() {
		$result = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerActionDoesNotExist() {
		$result = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'void', 'Content');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForClassIfSetToHardFail() {
		$this->setExpectedException('RuntimeException', NULL, 1364498093);
		ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Void', TRUE);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForActionIfSetToHardFail() {
		$this->setExpectedException('RuntimeException', NULL, 1364498223);
		ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'void', 'Content', FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassExists() {
		class_alias('FluidTYPO3\Flux\Controller\AbstractFluxController', 'Void\NoName\Controller\FakeController');
		$result = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Fake');
		$this->assertSame('Void\NoName\Controller\FakeController', $result);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassDoesNotExist() {
		$result = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectRequestArgumentsBasedOnPluginSignature() {
		$result = ResolveUtility::resolveOverriddenFluxControllerActionNameFromRequestParameters('tx_void_fake');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function canDetectWidgetTemplatePathAndFilenameAndTrimsTrailingSlash() {
		$templateRootPath = ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/');
		$expectedDefault = $templateRootPath . 'ViewHelpers/Widget/Grid/Index.html';
		$expectedLegacy = $templateRootPath . 'ViewHelpers/Widget/Grid/Legacy.html';
		$expectedWithGridelementsVersionTwo = $templateRootPath . 'ViewHelpers/Widget/Grid/GridElements.html';
		$utility = new ResolveUtility();
		ObjectAccess::setProperty($utility, 'initialized', TRUE, TRUE);
		ObjectAccess::setProperty($utility, 'isLegacyCoreVersion', FALSE, TRUE);
		$this->assertSame($expectedDefault, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', TRUE, TRUE);
		$this->assertSame($expectedWithGridelementsVersionTwo, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', FALSE, TRUE);
		ObjectAccess::setProperty($utility, 'isLegacyCoreVersion', TRUE, TRUE);
		$this->assertSame($expectedLegacy, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		ObjectAccess::setProperty($utility, 'initialized', FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canDetectCurrentPageRecord() {
		$result = ResolveUtility::resolveCurrentPageRecord();
		$this->assertNull($result);
		$expected = array('uid' => 99999999);
		$GLOBALS['TSFE'] = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
		$GLOBALS['TSFE']->page = $expected;
		$result = ResolveUtility::resolveCurrentPageRecord();
		$this->assertSame($result, $expected);
	}

}
