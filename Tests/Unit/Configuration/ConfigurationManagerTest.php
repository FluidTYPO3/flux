<?php
namespace FluidTYPO3\Flux\Configuration;
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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package FluidTYPO3\Flux
 */
class ConfigurationManagerTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function usesEnvironmentSettingToInstanciateCorrectConcreteInstanceForFrontend() {
		$environmentServiceMock = $this->getMock('TYPO3\CMS\Extbase\Service\EnvironmentService', array('isEnvironmentInFrontendMode'));
		$environmentServiceMock->expects($this->once())->method('isEnvironmentInFrontendMode')->will($this->returnValue(TRUE));
		$objectManagerMock = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager', array('get'));
		$objectManagerMock->expects($this->once())->method('get')->with('TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager');
		$mock = $this->getMock($this->createInstanceClassName());
		ObjectAccess::setProperty($mock, 'environmentService', $environmentServiceMock, TRUE);
		ObjectAccess::setProperty($mock, 'objectManager', $objectManagerMock, TRUE);
		$this->callInaccessibleMethod($mock, 'initializeConcreteConfigurationManager');
	}

	/**
	 * @test
	 */
	public function usesEnvironmentSettingToInstanciateCorrectConcreteInstanceForBackend() {
		$environmentServiceMock = $this->getMock('TYPO3\CMS\Extbase\Service\EnvironmentService', array('isEnvironmentInFrontendMode'));
		$environmentServiceMock->expects($this->once())->method('isEnvironmentInFrontendMode')->will($this->returnValue(FALSE));
		$objectManagerMock = $this->getMock('TYPO3\CMS\Extbase\Object\ObjectManager', array('get'));
		$objectManagerMock->expects($this->once())->method('get')->with('FluidTYPO3\Flux\Configuration\BackendConfigurationManager');
		$mock = $this->getMock($this->createInstanceClassName());
		ObjectAccess::setProperty($mock, 'environmentService', $environmentServiceMock, TRUE);
		ObjectAccess::setProperty($mock, 'objectManager', $objectManagerMock, TRUE);
		$this->callInaccessibleMethod($mock, 'initializeConcreteConfigurationManager');
	}

	/**
	 * @test
	 */
	public function setCurrentPageUidIgnoresFrontendConfigurationManager() {
		$mock = $this->getMock($this->createInstanceClassName(), array('initializeConcreteConfigurationManager'));
		$configurationManager = $this->getMock('TYPO3\\CMS\\Extbase\\Configuration\\FrontendConfigurationManager', array('setCurrentPageId'));
		$configurationManager->expects($this->never())->method('setCurrentPageId');
		ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, TRUE);
		$mock->setCurrentPageUid(1);
	}

	/**
	 * @test
	 */
	public function setCurrentPageUidUsesBackendConfigurationManager() {
		$mock = $this->getMock($this->createInstanceClassName(), array('initializeConcreteConfigurationManager'));
		$configurationManager = $this->getMock('FluidTYPO3\\Flux\\Configuration\\BackendConfigurationManager', array('setCurrentPageId'));
		$configurationManager->expects($this->once())->method('setCurrentPageId')->with(1);
		ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, TRUE);
		$mock->setCurrentPageUid(1);
	}

	/**
	 * @test
	 */
	public function getCurrentPageUidIgnoresFrontendConfigurationManager() {
		$mock = $this->getMock($this->createInstanceClassName(), array('initializeConcreteConfigurationManager'));
		$configurationManager = $this->getMock('TYPO3\\CMS\\Extbase\\Configuration\\FrontendConfigurationManager', array('getCurrentPageId'));
		$configurationManager->expects($this->never())->method('getCurrentPageId');
		ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, TRUE);
		$result = $mock->getCurrentPageId();
		$this->assertEquals(0, $result);
	}

	/**
	 * @test
	 */
	public function getCurrentPageUidUsesBackendConfigurationManager() {
		$mock = $this->getMock($this->createInstanceClassName(), array('initializeConcreteConfigurationManager'));
		$configurationManager = $this->getMock('FluidTYPO3\\Flux\\Configuration\\BackendConfigurationManager', array('getCurrentPageId'));
		$configurationManager->expects($this->once())->method('getCurrentPageId')->will($this->returnValue(1));
		ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, TRUE);
		$result = $mock->getCurrentPageId();
		$this->assertEquals(1, $result);
	}

}
