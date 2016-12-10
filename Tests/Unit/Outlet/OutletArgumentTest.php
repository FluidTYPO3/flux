<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\OutletArgument;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\StringConverter;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;

/**
 * OutletArgumentTest
 */
class OutletArgumentTest extends AbstractTestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] = [StringConverter::class];
        parent::setUp();
    }

    /**
     * @test
     */
    public function testConstructorSetsNameAndDataTypeProperties()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertAttributeSame('foobar', 'name', $argument);
        $this->assertAttributeSame('string', 'dataType', $argument);
    }

    /**
     * @test
     */
    public function testCanGetNameSetByConstructor()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertSame('foobar', $argument->getName());
    }

    /**
     * @test
     */
    public function testCanGetDataTypeSetByConstructor()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertSame('string', $argument->getDataType());
    }

    /**
     * @test
     */
    public function testGetValueReturnsValue()
    {
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $argument->setValue('testing');
        $this->assertSame('testing', $argument->getValue());
    }

    /**
     * @test
     */
    public function testSetValidators()
    {
        $validators = [new NotEmptyValidator()];
        $argument = new OutletArgument('foobar', 'string');
        $argument->setValidators($validators);
        $this->assertAttributeSame($validators, 'validators', $argument);
    }

    /**
     * @test
     */
    public function testGetValidators()
    {
        $validators = [new NotEmptyValidator()];
        $argument = new OutletArgument('foobar', 'string');
        $argument->setValidators($validators);
        $this->assertSame($validators, $argument->getValidators());
    }

    /**
     * @test
     */
    public function testAddValidator()
    {
        $validators = [new NotEmptyValidator()];
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $argument->addValidator('NotEmpty', []);
        $this->assertAttributeEquals($validators, 'validators', $argument);
    }

    /**
     * @test
     */
    public function testSetValueValidatesUsingValidator()
    {
        $validator = $this->getMockBuilder(NotEmptyValidator::class)->setMethods(['validate'])->getMock();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn(new Result());
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
    }

    /**
     * @test
     */
    public function testGetValidationResultsReturnsNullBeforeSetValue()
    {
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $this->assertNull($argument->getValidationResults());
    }

    /**
     * @test
     */
    public function testGetValidationResultsReturnsResultsAfterSetValue()
    {
        $validator = $this->getMockBuilder(NotEmptyValidator::class)->setMethods(['validate'])->getMock();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn(new Result());
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
        $this->assertInstanceOf(Result::class, $argument->getValidationResults());
    }

    /**
     * @test
     */
    public function testIsValidReturnsTrueBeforeSetValue()
    {
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $this->assertTrue($argument->isValid());
    }

    /**
     * @test
     */
    public function testIsValidReturnsResultErrorStatusAfterSetValue()
    {
        $result = new Result();
        $result->addError(new Error('Some error', 123));
        $validator = $this->getMockBuilder(NotEmptyValidator::class)->setMethods(['validate'])->getMock();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn($result);
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
        $this->assertFalse($argument->isValid());
    }

    /**
     * @test
     */
    public function testInjectsAndCanReturnPropertyMappingConfiguration()
    {
        $argument = $this->objectManager->get(OutletArgument::class, 'foobar', 'string');
        $this->assertInstanceOf(PropertyMappingConfigurationInterface::class, $argument->getPropertyMappingConfiguration());
    }

    /**
     * @test
     */
    public function testConstructorThrowsExceptionIfNameIsNotString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new OutletArgument(123, 'string');
    }
    /**
     * @test
     */
    public function testConstructorThrowsExceptionIfNameIsEmpty()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new OutletArgument('', 'string');
    }
}
