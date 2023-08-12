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
use TYPO3\CMS\Core\Service\FlexFormService;

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
     * @test
     */
    public function canInstantiateFluxService()
    {
        $service = $this->createFluxServiceInstance();
        $this->assertInstanceOf(FluxService::class, $service);
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
        $instance = $this->createFluxServiceInstance();
        $instance->setFlexFormService($flexFormService);

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getConvertFlexFormContentToArrayTestValues()
    {
        $form = $this->getMockBuilder(Form::class)->setMethods(['dummy'])->getMock();
        return [
            ['', null, '', '', []],
            ['', $form, '', '', []],
            [Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD, $form, '', '', ['settings' => ['input' => 0]]]
        ];
    }

    public function testConvertFlexFormContentToArrayWithTransform(): void
    {
        $expected = [
            'foo' => 'bar',
        ];

        $flexFormContent = 'abc';
        $languagePointer = null;
        $valuePointer = null;

        $form = Form::create();
        $form->setOption(Form::OPTION_TRANSFORM, true);

        $instance = $this->createFluxServiceInstance();

        /** @var class-string $serviceClassName */
        $serviceClassName = class_exists(FlexFormService::class)
            ? FlexFormService::class
            : \TYPO3\CMS\Extbase\Service\FlexFormService::class;
        $flexFormService = $this->getMockBuilder($serviceClassName)
            ->setMethods(['convertFlexFormContentToArray'])
            ->disableOriginalConstructor()
            ->getMock();
        $flexFormService->method('convertFlexFormContentToArray')->willReturn($expected);

        $dataTransformer = $this->getMockBuilder(Form\Transformation\FormDataTransformer::class)
            ->setMethods(['transformAccordingToConfiguration'])
            ->disableOriginalConstructor()
            ->getMock();
        $dataTransformer->method('transformAccordingToConfiguration')->willReturnArgument(0);

        $instance->setFlexFormService($flexFormService);
        $instance->setFormDataTransformer($dataTransformer);

        $result = $instance->convertFlexFormContentToArray($flexFormContent, $form, $languagePointer, $valuePointer);
        $this->assertEquals($expected, $result);
    }
}
