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
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

/**
 * FluxServiceTest
 */
class FluxServiceTest extends AbstractTestCase
{

    /**
     * Setup
     */
    public function setup()
    {
        $providers = Core::getRegisteredFlexFormProviders();
        if (true === in_array('FluidTYPO3\Flux\Service\FluxService', $providers)) {
            Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Service\FluxService');
        }
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
        $this->assertInstanceOf('FluidTYPO3\Flux\Service\FluxService', $service);
    }

    /**
     * @test
     */
    public function canResolvePrimaryConfigurationProviderWithEmptyArray()
    {
        $service = $this->createFluxServiceInstance();
        $service->injectProviderResolver($this->objectManager->get('FluidTYPO3\\Flux\\Provider\\ProviderResolver'));
        $result = $service->resolvePrimaryConfigurationProvider('tt_content', null);
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function testGetSettingsForExtensionName()
    {
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(array('getTypoScriptByPath'))->getMock();
        $instance->expects($this->once())->method('getTypoScriptByPath')
            ->with('plugin.tx_underscore.settings')
            ->willReturn(array('test' => 'test'));
        $result = $instance->getSettingsForExtensionName('under_score');
        $this->assertEquals(array('test' => 'test'), $result);
    }

    /**
     * @test
     */
    public function messageIgnoresRepeatedMessages()
    {
        $instance = $this->getMockBuilder('FluidTYPO3\\Flux\\Service\\FluxService')->setMethods(['dummy'])->getMock();
        $expected = ['TestTest' => true];
        $instance->message('Test', 'Test', 2);
        $this->assertAttributeSame($expected, 'sentDebugMessages', $instance);
        $instance->message('Test', 'Test', 2);
        $this->assertAttributeSame($expected, 'sentDebugMessages', $instance);
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
        $instance = $this->createInstance();
        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFlexFormContentToArrayTestValues()
    {
        return array(
            array('', null, '', '', array()),
            array('', Form::create(), '', '', array()),
            array(Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, Form::create(), '', '', array('settings' => array('input' => 0)))
        );
    }

    /**
     * @test
     */
    public function testGetAllTypoScriptCache()
    {
        $fluxService = $this->createFluxServiceInstance(array('getCurrentPageId'));

        $configurationManager = $this->getMockBuilder('FluidTYPO3\Flux\Configuration\ConfigurationManager')->setMethods(array('getConfiguration'))->getMock();
        $fluxService->injectConfigurationManager($configurationManager);
        $configurationManager->expects($this->once(0))->method('getConfiguration')->willReturn(['foo' => 'bar']);

        $this->assertNotNull($fluxService->getAllTypoScript());
        $this->assertNotNull($fluxService->getAllTypoScript());
    }
}
