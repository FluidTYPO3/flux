<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\Outlet\Pipe\AbstractPipeTestCase;

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
        $instance = $this->createInstance();
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
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
        $instance = $this->createInstance();
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter';
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType('TYPO3\CMS\Domain\Model\FrontendUser');
        $this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception', '', 1386292424);
        $instance->conduct($this->defaultData);
    }

    /**
     * @test
     */
    public function conductingDataThrowsPipeExceptionWhenTypeConverterReturnsError()
    {
        $instance = $this->createInstance();
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\FloatConverter';
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType('float');
        if ('6.0' !== substr(TYPO3_version, 0, 3)) {
            $this->setExpectedException('FluidTYPO3\Flux\Outlet\Pipe\Exception');
        }
        $instance->conduct('test');
    }

    /**
     * @test
     */
    public function conductingDataPassesThroughExceptionWhenTypeConverterFails()
    {
        $instance = $this->createInstance();
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter';
        $converter = $this->objectManager->get($converterClass);
        $instance->setTypeConverter($converter);
        $instance->setTargetType('DateTime');
        $this->setExpectedException('TYPO3\CMS\Extbase\Property\Exception\TypeConverterException', '', 1308003914);
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
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
        $converter = $this->objectManager->get($converterClass);
        $this->assertGetterAndSetterWorks('typeConverter', $converter, $converter, true);
    }

    /**
     * @test
     */
    public function canGetAndSetTypeConverterAndCreatesInstanceIfClassName()
    {
        $converterClass = 'TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter';
        $converter = $this->objectManager->get($converterClass);
        $this->assertGetterAndSetterWorks('typeConverter', $converterClass, $converter, true);
    }
}
