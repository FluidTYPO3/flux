<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class ResolveUtilityTest extends AbstractTestCase {

	/**
	 * @disabledtest
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
	 * @disabledtest
	 */
	public function canDetectWidgetTemplatePathAndFilenameAndTrimsTrailingSlash() {
		$templateRootPath = ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/');
		$expectedDefault = $templateRootPath . 'ViewHelpers/Widget/Grid/Index.html';
		$expectedWithGridelementsVersionTwo = $templateRootPath . 'ViewHelpers/Widget/Grid/GridElements.html';
		$utility = new ResolveUtility();
		ObjectAccess::setProperty($utility, 'initialized', TRUE, TRUE);
		$this->assertSame($expectedDefault, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', TRUE, TRUE);
		$this->assertSame($expectedWithGridelementsVersionTwo, $utility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath));
		ObjectAccess::setProperty($utility, 'hasGridElementsVersionTwo', FALSE, TRUE);
		ObjectAccess::setProperty($utility, 'initialized', FALSE, TRUE);
	}

	/**
	 * @disabledtest
	 */
	public function canDetectCurrentPageRecord() {
		$expected = reset($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'pages', 'pid=0'));
		$GLOBALS['TSFE'] = new \stdClass();
		$GLOBALS['TSFE']->page = $expected;
		$GLOBALS['TSFE']->id = $expected['uid'];
		$result = ResolveUtility::resolveCurrentPageRecord();
		$this->assertSame($result, $expected);
		unset($GLOBALS['TSFE']);
	}

	/**
	 * @disabledtest
	 */
	public function resolvePossibleOverlayTemplateFileDetectsOverlayFile() {
		$overlays = array(
			array('templateRootPath' => 'EXT:flux/Resources/Private/Templates/SomeFolder/'),
			array('templateRootPath' => 'EXT:flux/Resources/Private/Templates/'),
			array('templateRootPath' => 'EXT:flux/Resources/Private/Templates/ViewHelpers/Widget/')
		);
		$controller = 'Grid';
		$action = 'index';
		$format = 'html';
		$result = ResolveUtility::resolvePossibleOverlayTemplateFile($overlays, $controller, $action, $format);
		$expected = GeneralUtility::getFileAbsFileName('EXT:flux/Resources/Private/Templates/ViewHelpers/Widget/Grid/Index.html');
		$this->assertEquals($expected, $result);
	}

}
