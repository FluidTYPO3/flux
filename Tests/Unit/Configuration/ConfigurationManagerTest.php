<?php
namespace FluidTYPO3\Flux\Configuration;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * ConfigurationManagerTest
 */
class ConfigurationManagerTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function usesEnvironmentSettingToInstanciateCorrectConcreteInstanceForFrontend()
    {
        $environmentServiceMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Service\EnvironmentService')->setMethods(array('isEnvironmentInFrontendMode'))->getMock();
        $environmentServiceMock->expects($this->once())->method('isEnvironmentInFrontendMode')->will($this->returnValue(true));
        $objectManagerMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Object\ObjectManager')->setMethods(array('get'))->getMock();
        $objectManagerMock->expects($this->once())->method('get')->with('FluidTYPO3\Flux\Configuration\FrontendConfigurationManager');
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        ObjectAccess::setProperty($mock, 'environmentService', $environmentServiceMock, true);
        ObjectAccess::setProperty($mock, 'objectManager', $objectManagerMock, true);
        $this->callInaccessibleMethod($mock, 'initializeConcreteConfigurationManager');
    }

    /**
     * @test
     */
    public function usesEnvironmentSettingToInstanciateCorrectConcreteInstanceForBackend()
    {
        $environmentServiceMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Service\EnvironmentService')->setMethods(array('isEnvironmentInFrontendMode'))->getMock();
        $environmentServiceMock->expects($this->once())->method('isEnvironmentInFrontendMode')->will($this->returnValue(false));
        $objectManagerMock = $this->getMockBuilder('TYPO3\CMS\Extbase\Object\ObjectManager')->setMethods(array('get'))->getMock();
        $objectManagerMock->expects($this->once())->method('get')->with('FluidTYPO3\Flux\Configuration\BackendConfigurationManager');
        $mock = $this->getMockBuilder($this->createInstanceClassName())->getMock();
        ObjectAccess::setProperty($mock, 'environmentService', $environmentServiceMock, true);
        ObjectAccess::setProperty($mock, 'objectManager', $objectManagerMock, true);
        $this->callInaccessibleMethod($mock, 'initializeConcreteConfigurationManager');
    }

    /**
     * @test
     */
    public function setCurrentPageUidIgnoresFrontendConfigurationManager()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('initializeConcreteConfigurationManager'))->getMock();
        $configurationManager = $this->getMockBuilder('TYPO3\\CMS\\Extbase\\Configuration\\FrontendConfigurationManager')->setMethods(array('setCurrentPageId'))->getMock();
        $configurationManager->expects($this->never())->method('setCurrentPageId');
        ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, true);
        $mock->setCurrentPageUid(1);
    }

    /**
     * @test
     */
    public function setCurrentPageUidUsesBackendConfigurationManager()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('initializeConcreteConfigurationManager'))->getMock();
        $configurationManager = $this->getMockBuilder('FluidTYPO3\\Flux\\Configuration\\BackendConfigurationManager')->setMethods(array('setCurrentPageId'))->getMock();
        $configurationManager->expects($this->once())->method('setCurrentPageId')->with(1);
        ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, true);
        $mock->setCurrentPageUid(1);
    }

    /**
     * @test
     */
    public function getCurrentPageUidIgnoresFrontendConfigurationManager()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('initializeConcreteConfigurationManager'))->getMock();
        $configurationManager = $this->getMockBuilder('TYPO3\\CMS\\Extbase\\Configuration\\FrontendConfigurationManager')->setMethods(array('getCurrentPageId'))->getMock();
        $configurationManager->expects($this->never())->method('getCurrentPageId');
        ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, true);
        $result = $mock->getCurrentPageId();
        $this->assertEquals(0, $result);
    }

    /**
     * @test
     */
    public function getCurrentPageUidUsesBackendConfigurationManager()
    {
        $mock = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('initializeConcreteConfigurationManager'))->getMock();
        $configurationManager = $this->getMockBuilder('FluidTYPO3\\Flux\\Configuration\\BackendConfigurationManager')->setMethods(array('getCurrentPageId'))->getMock();
        $configurationManager->expects($this->once())->method('getCurrentPageId')->will($this->returnValue(1));
        ObjectAccess::setProperty($mock, 'concreteConfigurationManager', $configurationManager, true);
        $result = $mock->getCurrentPageId();
        $this->assertEquals(1, $result);
    }
}
