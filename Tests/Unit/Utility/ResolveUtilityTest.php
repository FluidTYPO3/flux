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
	 * @test
	 */
	public function resolvesClassNamesInSubNamespaceOfPackage() {
		$result = ResolveUtility::resolveClassNamesInPackageSubNamespace('FluidTYPO3.Flux', '');
		$this->assertEquals(array('FluidTYPO3\\Flux\\Core', 'FluidTYPO3\\Flux\\Form'), $result);
	}

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
	 * @dataProvider getClassToTableTestValues
	 * @param string $class
	 * @param string $expectedTable
	 */
	public function testResolveTableName($class, $expectedTable) {
		$result = ResolveUtility::resolveDatabaseTableName($class);
		$this->assertEquals($expectedTable, $result);
	}

	/**
	 * @return array
	 */
	public function getClassToTableTestValues() {
		return array(
			array('syslog', 'syslog'),
			array('FluidTYPO3\\Flux\\Domain\\Model\\ObjectName', 'tx_flux_domain_model_objectname'),
			array('TYPO3\\CMS\\Extbase\\Domain\\Model\\ObjectName', 'tx_extbase_domain_model_objectname'),
			array('Tx_Flux_Domain_Model_ObjectName', 'tx_flux_domain_model_objectname'),
		);
	}

}
