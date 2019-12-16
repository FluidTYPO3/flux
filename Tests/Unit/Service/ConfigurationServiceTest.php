<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\ConfigurationService;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Core;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationServiceTest
 */
class ConfigurationServiceTest extends AbstractTestCase
{

    /**
     * @return void
     */
    public function testPerformsInjections()
    {
        $instance = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager')
            ->get(ConfigurationService::class);
        $this->assertAttributeInstanceOf('TYPO3\\CMS\\Core\\Resource\\ResourceFactory', 'resourceFactory', $instance);
    }

    /**
     * @dataProvider getConvertFileReferenceToTemplatePathAndFilenameTestValues
     * @param string $reference
     * @param string|NULL $resourceFactoryOutput
     * @param string $expected
     * @return void
     */
    public function testConvertFileReferenceToTemplatePathAndFilename($reference, $resourceFactoryOutput, $expected)
    {
        $instance = new ConfigurationService();
        if (null !== $resourceFactoryOutput) {
            /** @var ResourceFactory|\PHPUnit_Framework_MockObject_MockObject $resourceFactory */
            $resourceFactory = $this->getMockBuilder(
                ResourceFactory::class
            )->setMethods(
                array('getFileObjectFromCombinedIdentifier')
            )->getMock();
            $resourceFactory->expects($this->once())->method('getFileObjectFromCombinedIdentifier')
                ->with($reference)->willReturn($resourceFactoryOutput);
            $instance->injectResourceFactory($resourceFactory);
        }
        $result = $instance->convertFileReferenceToTemplatePathAndFilename($reference);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFileReferenceToTemplatePathAndFilenameTestValues()
    {
        $relativeReference = 'Tests/Fixtures/Templates/Page/Dummy.html';
        return array(
            array($relativeReference, null, GeneralUtility::getFileAbsFileName($relativeReference)),
            array('1', $relativeReference, $relativeReference),
        );
    }

    /**
     * @dataProvider getViewConfigurationByFileReferenceTestValues
     * @param string $reference
     * @param string $expectedParameter
     * @return void
     */
    public function testGetViewConfigurationByFileReference($reference, $expectedParameter)
    {
        $instance = new ConfigurationService();
        $result = $instance->getViewConfigurationByFileReference($reference);
        $this->assertEquals($expectedParameter, $result);
    }

    /**
     * @return array
     */
    public function getViewConfigurationByFileReferenceTestValues()
    {
        $fluxPaths = [
            'templateRootPaths' => [ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/')],
            'partialRootPaths' => [ExtensionManagementUtility::extPath('flux', 'Resources/Private/Partials/')],
            'layoutRootPaths' => [ExtensionManagementUtility::extPath('flux', 'Resources/Private/Layouts/')],
        ];
        return array(
            array('some/file', $fluxPaths),
            array('EXT:flux/some/file', $fluxPaths),
        );
    }

    /**
     * @dataProvider getPageConfigurationInvalidTestValues
     * @param mixed $input
     * @return void
     */
    public function testGetPageConfigurationReturnsEmptyArrayOnInvalidInput($input)
    {
        $instance = new ConfigurationService();
        $result = $instance->getPageConfiguration($input);
        $this->assertEquals(array(), $result);
    }

    /**
     * @return array
     */
    public function getPageConfigurationInvalidTestValues()
    {
        return array(
            array(''),
            array(0),
            array(array()),
        );
    }

    /**
     * @return void
     */
    public function testGetPageConfigurationWithoutExtensionNameReadsRegisteredProviders()
    {
        $instance = new ConfigurationService();
        Core::registerProviderExtensionKey('foo', 'Page');
        Core::registerProviderExtensionKey('bar', 'Page');
        $result = $instance->getPageConfiguration();
        $this->assertCount(3, $result);
    }
}
