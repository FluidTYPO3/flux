<?php
namespace FluidTYPO3\Flux\Tests\Unit\Service;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\View\TemplatePaths;

/**
 * FluxServiceTest
 */
class FluxServiceTest extends AbstractTestCase
{
    /**
     * Setup
     */
    public function setup(): void
    {
        parent::setUp();

        $providers = Core::getRegisteredFlexFormProviders();
        if (true === in_array(FluxService::class, $providers)) {
            Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Service\FluxService');
        }
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
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(['resolveAbsolutePathForFilename'])->getMock();
        $instance->method('resolveAbsolutePathForFilename')->willReturnArgument(0);
        if (null !== $resourceFactoryOutput) {
            $file = $this->getMockBuilder(File::class)->setMethods(['getIdentifier'])->disableOriginalConstructor()->getMock();
            $file->method('getIdentifier')->willReturn($resourceFactoryOutput);
            /** @var ResourceFactory|MockObject $resourceFactory */
            $resourceFactory = $this->getMockBuilder(
                ResourceFactory::class
            )->setMethods(
                array('getFileObjectFromCombinedIdentifier')
            )->disableOriginalConstructor()->getMock();
            $resourceFactory->expects($this->once())->method('getFileObjectFromCombinedIdentifier')
                ->with($reference)->willReturn($file);
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
            array($relativeReference, null, $relativeReference),
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
        $templatePaths = $this->getMockBuilder(TemplatePaths::class)->setMethods(['toArray'])->getMock();
        $templatePaths->method('toArray')->willReturn($expectedParameter);
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(['createTemplatePaths'])->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        $result = $instance->getViewConfigurationByFileReference($reference);
        $this->assertEquals($expectedParameter, $result);
    }

    /**
     * @return array
     */
    public function getViewConfigurationByFileReferenceTestValues()
    {
        $fluxPaths = [
            'templateRootPaths' => ['Resources/Private/Templates/'],
            'partialRootPaths' => ['Resources/Private/Partials/'],
            'layoutRootPaths' => ['Resources/Private/Layouts/'],
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
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(['getLogger'])->getMock();
        $instance->method('getLogger')->willReturn($this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass());
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
        $templatePaths = new TemplatePaths();
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(['createTemplatePaths'])->getMock();
        $instance->method('createTemplatePaths')->willReturn($templatePaths);
        Core::registerProviderExtensionKey('foo', 'Page');
        Core::registerProviderExtensionKey('bar', 'Page');
        $result = $instance->getPageConfiguration();
        $this->assertCount(2, $result);
    }

    /**
     * @test
     * @dataProvider getSortObjectsTestValues
     * @param array $input
     * @param string $sortBy
     * @param string $direction
     * @param array $expectedOutput
     */
    public function testSortObjectsByProperty($input, $sortBy, $direction, $expectedOutput)
    {
        $service = new FluxService();
        $sorted = $service->sortObjectsByProperty($input, $sortBy, $direction);
        $this->assertSame($expectedOutput, $sorted);
    }

    /**
     * @return array
     */
    public function getSortObjectsTestValues()
    {
        return array(
            array(
                array(array('foo' => 'b'), array('foo' => 'a')),
                'foo', 'ASC',
                array(1 => array('foo' => 'a'), 0 => array('foo' => 'b'))
            ),
            array(
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
                'foo', 'ASC',
                array('a2' => array('foo' => 'a'), 'a1' => array('foo' => 'b')),
            ),
            array(
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
                'foo', 'DESC',
                array('a1' => array('foo' => 'b'), 'a2' => array('foo' => 'a')),
            ),
        );
    }

    /**
     * @test
     */
    public function canInstantiateFluxService()
    {
        $service = $this->createFluxServiceInstance();
        $this->assertInstanceOf(FluxService::class, $service);
    }

    /**
     * @test
     */
    public function canResolvePrimaryConfigurationProviderWithEmptyArray()
    {
        $service = $this->createFluxServiceInstance();
        $service->injectProviderResolver($this->getMockBuilder(ProviderResolver::class)->getMock());
        $result = $service->resolvePrimaryConfigurationProvider('foobar', null);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function testGetTypoScriptByPath()
    {
        $service = new FluxService();
        ;
        $configurationManager = $this->getMockBuilder(ConfigurationManager::class)->setMethods(array('getConfiguration'))->disableOriginalConstructor()->getMock();
        $configurationManager->expects($this->once())->method('getConfiguration')
            ->with(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT)
            ->willReturn(array('plugin.' => array('tx_test.' => array('settings.' => array('test_var' => 'test_val')))));
        $service->injectConfigurationManager($configurationManager);
        $result = $service->getTypoScriptByPath('plugin.tx_test.settings');
        $this->assertEquals(array('test_var' => 'test_val'), $result);
    }

    /**
     * @test
     */
    public function testGetSettingsForExtensionName()
    {
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(array('getTypoScriptByPath'))->getMock();
        $instance->expects($this->once())->method('getTypoScriptByPath')
            ->with('plugin.tx_underscore.settings')
            ->willReturn(array('test' => 'test'));
        $result = $instance->getSettingsForExtensionName('under_score');
        $this->assertEquals(array('test' => 'test'), $result);
    }

    /**
     * @test
     * @dataProvider getConvertFlexFormContentToArrayTestValues
     * @param string $flexFormContent
     * @param Form|NULL $form
     * @param string|NULL $languagePointer
     * @param string|NULL $valuePointer
     * @param array $expected
     */
    public function testConvertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer, $expected)
    {
        /** @var class-string $serviceClassName */
        $serviceClassName = class_exists(FlexFormService::class) ? FlexFormService::class : \TYPO3\CMS\Extbase\Service\FlexFormService::class;
        $flexFormService = $this->getMockBuilder($serviceClassName)->setMethods(['convertFlexFormContentToArray'])->disableOriginalConstructor()->getMock();
        $flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);
        $instance = $this->getMockBuilder(FluxService::class)->setMethods(['getFlexFormService'])->getMock();
        $instance->method('getFlexFormService')->willReturn($flexFormService);

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFlexFormContentToArrayTestValues()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        return array(
            array('', null, '', '', array()),
            array('', $form, '', '', array()),
            array(Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, $form, '', '', array('settings' => array('input' => 0)))
        );
    }
}
