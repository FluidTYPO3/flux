<?php
namespace FluidTYPO3\Flux\Tests\Unit\Helpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Helper\Resolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ResolveUtilityTest
 */
class ResolveUtilityTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function testResolveDomainFormClassInstancesFromPackages() {
		$modelClassName = 'FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\Domain\\Model\\Dummy';
		Core::registerAutoFormForModelObjectClassName($modelClassName);
		$classNames = array('FluidTYPO3\\Flux\\Tests\\Fixtures\\Classes\\Domain\\Form\\DummyForm');
		$resolver = $this->getMock('FluidTYPO3\\Flux\\Helper\\Resolver', array('resolveClassNamesInPackageSubNamespace'));
		$resolver->expects($this->once())->method('resolveClassNamesInPackageSubNamespace')->willReturn($classNames);
		$result = $resolver->resolveDomainFormClassInstancesFromPackages(array('foobar'));
		$this->assertInstanceOf($classNames[0], $result[$modelClassName]);
	}

	/**
	 * @test
	 */
	public function resolvesClassNamesInSubNamespaceOfPackage() {
		$resolver = new Resolver();
		$result = $resolver->resolveClassNamesInPackageSubNamespace('FluidTYPO3.Flux', '');
		$this->assertEquals(array('FluidTYPO3\\Flux\\Core', 'FluidTYPO3\\Flux\\FluxPackage', 'FluidTYPO3\\Flux\\Form'), $result);
	}

	/**
	 * @test
	 */
	public function returnsClassIfClassExists() {
		$resolver = new Resolver();
		$className = $resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Content');
		$instance = $this->objectManager->get($className);
		$this->assertInstanceOf('FluidTYPO3\Flux\Controller\AbstractFluxController', $instance);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerClassNameDoesNotExist() {
		$resolver = new Resolver();
		$result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function returnsNullIfControllerActionDoesNotExist() {
		$resolver = new Resolver();
		$result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'void', 'Content');
		$this->assertNull($result);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForClassIfSetToHardFail() {
		$resolver = new Resolver();
		$this->setExpectedException('RuntimeException', '', 1364498093);
		$resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Void', TRUE);
	}

	/**
	 * @test
	 */
	public function throwsExceptionForActionIfSetToHardFail() {
		$resolver = new Resolver();
		$this->setExpectedException('RuntimeException', '', 1364498223);
		$resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'void', 'Content', FALSE, TRUE);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassExists() {
		$resolver = new Resolver();
		class_alias('FluidTYPO3\Flux\Controller\AbstractFluxController', 'Void\NoName\Controller\FakeController');
		$result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Fake');
		$this->assertSame('Void\NoName\Controller\FakeController', $result);
	}

	/**
	 * @test
	 */
	public function canDetectControllerClassPresenceFromExtensionKeyAndControllerTypeWithVendorNameWhenClassDoesNotExist() {
		$resolver = new Resolver();
		$result = $resolver->resolveFluxControllerClassNameByExtensionKeyAndAction('Void.NoName', 'render', 'Void');
		$this->assertNull($result);
	}

	/**
	 * @test
	 * @dataProvider getClassToTableTestValues
	 * @param string $class
	 * @param string $expectedTable
	 */
	public function testResolveTableName($class, $expectedTable) {
		$resolver = new Resolver();
		$result = $resolver->resolveDatabaseTableName($class);
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
