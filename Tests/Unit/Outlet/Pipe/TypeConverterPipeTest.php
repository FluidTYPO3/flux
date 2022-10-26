<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\TypeConverterPipe;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;

/**
 * TypeConverterPipeTest
 */
class TypeConverterPipeTest extends AbstractPipeTestCase
{
    /**
     * @var array
     */
    protected $defaultData = [
        'targetType' => 'float',
    ];

    protected function createInstance()
    {
        /** @var TypeConverterPipe $instance */
        $instance = parent::createInstance();
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->method('get')->willReturn(new StringConverter());
        $instance->injectObjectManager($objectManager);
        return $instance;
    }

    /**
     * @test
     */
    public function canConductData()
    {
        $instance = $this->createInstance();
        $converterClass = StringConverter::class;
        $converter = new $converterClass();
        $instance->setTypeConverter($converter);
        $instance->setTargetType('string');
        $output = $instance->conduct('test');
        $this->assertEquals('test', $output);
    }

    /**
     * @test
     */
    public function conductingDataThrowsExceptionWhenTypeConverterCannotConvertToTargetType()
    {
        $instance = $this->createInstance();
        $converterClass = DateTimeConverter::class;
        $converter = new $converterClass();
        $instance->setTypeConverter($converter);
        $instance->setTargetType('TYPO3\CMS\Domain\Model\FrontendUser');
        $this->expectExceptionCode(1386292424);
        $instance->conduct($this->defaultData);
    }

    /**
     * @test
     */
    public function conductingDataThrowsPipeExceptionWhenTypeConverterReturnsError()
    {
        $instance = $this->createInstance();
        $converterClass = FloatConverter::class;
        $converter = new $converterClass();
        $instance->setTypeConverter($converter);
        $instance->setTargetType('float');
        $this->expectException('FluidTYPO3\Flux\Outlet\Pipe\Exception');
        $instance->conduct('test');
    }

    /**
     * @test
     */
    public function conductingDataPassesThroughExceptionWhenTypeConverterFails()
    {
        $instance = $this->createInstance();
        $converterClass = DateTimeConverter::class;
        $converter = new $converterClass();
        $instance->setTypeConverter($converter);
        $instance->setTargetType('DateTime');
        $this->expectExceptionCode(1308003914);
        $instance->conduct([]);
    }

    /**
     * @test
     */
    public function canGetAndSetTargetType()
    {
        $this->assertGetterAndSetterWorks('targetType', 'string', 'string', true);
    }

    /**
     * @test
     */
    public function canGetAndSetTypeConverter()
    {
        $converterClass = StringConverter::class;
        $converter = new $converterClass();
        $this->assertGetterAndSetterWorks('typeConverter', $converter, $converter, true);
    }

    /**
     * @test
     */
    public function canGetAndSetTypeConverterAndCreatesInstanceIfClassName()
    {
        $converterClass = StringConverter::class;
        $converter = new $converterClass();
        $this->assertGetterAndSetterWorks('typeConverter', $converterClass, $converter, true);
    }
}
