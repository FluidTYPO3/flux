<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\Exception;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
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
    protected $defaultData = array(
        'targetType' => 'float',
    );

    /**
     * @test
     */
    public function canConductData()
    {
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $converterClass = StringConverter::class;
        $converter = $this->objectManager->get($converterClass);
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
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $converterClass = DateTimeConverter::class;
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType(FrontendUser::class);
        $this->expectExceptionCode(1386292424);
        $instance->conduct($this->defaultData);
    }

    /**
     * @test
     */
    public function conductingDataThrowsPipeExceptionWhenTypeConverterReturnsError()
    {
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $converterClass = FloatConverter::class;
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType('float');
        $this->expectException(Exception::class);
        $instance->conduct('test');
    }

    /**
     * @test
     */
    public function conductingDataPassesThroughExceptionWhenTypeConverterFails()
    {
        $this->markTestSkipped();
        $instance = $this->createInstance();
        $converterClass = DateTimeConverter::class;
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType('DateTime');
        $this->expectExceptionCode(1308003914);
        $instance->conduct(array());
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
        $this->markTestSkipped();
        $converterClass = StringConverter::class;
        $converter = $this->objectManager->get($converterClass);
        $this->assertGetterAndSetterWorks('typeConverter', $converter, $converter, true);
    }

    /**
     * @test
     */
    public function canGetAndSetTypeConverterAndCreatesInstanceIfClassName()
    {
        $this->markTestSkipped();
        $converterClass = StringConverter::class;
        $converter = $this->objectManager->get($converterClass);
        $this->assertGetterAndSetterWorks('typeConverter', $converterClass, $converter, true);
    }
}
