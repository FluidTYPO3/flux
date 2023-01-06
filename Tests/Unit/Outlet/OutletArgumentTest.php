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
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * OutletArgumentTest
 */
class OutletArgumentTest extends AbstractTestCase
{
    /**
     * @var PropertyMapper&MockObject
     */
    protected $propertyMapper;

    /**
     * @var ValidatorResolver&MockObject
     */
    protected $validatorResolver;

    /**
     * @var MvcPropertyMappingConfiguration&MockObject
     */
    protected $propertyMappingConfiguration;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->propertyMappingConfiguration = $this->getMockBuilder(MvcPropertyMappingConfiguration::class)->getMock();

        $this->propertyMapper = $this->getMockBuilder(PropertyMapper::class)->setMethods(['convert', 'getMessages'])->disableOriginalConstructor()->getMock();
        $this->propertyMapper->method('convert')->willReturnArgument(0);
        $this->propertyMapper->method('getMessages')->willReturn(new Result());

        $this->validatorResolver = $this->getMockBuilder(ValidatorResolver::class)->setMethods(['createValidator'])->disableOriginalConstructor()->getMock();
        $this->validatorResolver->method('createValidator')->willReturnMap(
            [
                ['NotEmpty', [], new NotEmptyValidator()],
            ]
        );
    }

    /**
     * @test
     */
    public function testConstructorSetsNameAndDataTypeProperties()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertSame('foobar', $argument->getName());
        $this->assertSame('string', $argument->getDataType());
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
        $argument = new OutletArgument('foobar', 'string');
        $argument->injectPropertyMapper($this->propertyMapper);
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
        $this->assertSame($validators, $argument->getValidators());
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
        $argument = new OutletArgument('foobar', 'string');
        $argument->injectValidatorResolver($this->validatorResolver);
        $argument->addValidator('NotEmpty', []);
        $this->assertEquals($validators, $this->getInaccessiblePropertyValue($argument, 'validators'));
    }

    /**
     * @test
     */
    public function testSetValueValidatesUsingValidator()
    {
        $validator = $this->getMockBuilder(ValidatorInterface::class)->setMethods(['validate'])->getMockForAbstractClass();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn(new Result());
        $argument = new OutletArgument('foobar', 'string');
        $argument->injectPropertyMapper($this->propertyMapper);
        $argument->injectPropertyMappingConfiguration($this->propertyMappingConfiguration);
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
    }

    /**
     * @test
     */
    public function testGetValidationResultsReturnsNullBeforeSetValue()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertNull($argument->getValidationResults());
    }

    /**
     * @test
     */
    public function testGetValidationResultsReturnsResultsAfterSetValue()
    {
        $validator = $this->getMockBuilder(ValidatorInterface::class)->setMethods(['validate'])->getMockForAbstractClass();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn(new Result());
        $argument = new OutletArgument('foobar', 'string');
        $argument->injectPropertyMapper($this->propertyMapper);
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
        $this->assertInstanceOf(Result::class, $argument->getValidationResults());
    }

    /**
     * @test
     */
    public function testIsValidReturnsTrueBeforeSetValue()
    {
        $argument = new OutletArgument('foobar', 'string');
        $this->assertTrue($argument->isValid());
    }

    /**
     * @test
     */
    public function testIsValidReturnsResultErrorStatusAfterSetValue()
    {
        $result = new Result();
        $result->addError(new Error('Some error', 123));
        $validator = $this->getMockBuilder(ValidatorInterface::class)->setMethods(['validate'])->getMockForAbstractClass();
        $validator->expects($this->once())->method('validate')->with('stringvalue')->willReturn($result);
        $argument = new OutletArgument('foobar', 'string');
        $argument->injectPropertyMapper($this->propertyMapper);
        $argument->setValidators([$validator]);
        $argument->setValue('stringvalue');
        $this->assertFalse($argument->isValid());
    }

    /**
     * @test
     */
    public function testConstructorThrowsExceptionIfNameIsNotString()
    {
        $this->expectException(\InvalidArgumentException::class);
        new OutletArgument(123, 'string');
    }
    /**
     * @test
     */
    public function testConstructorThrowsExceptionIfNameIsEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        new OutletArgument('', 'string');
    }

    public function testThrowsExceptionOnAddingInvalidValidator(): void
    {
        $singletons = GeneralUtility::getSingletonInstances();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMockForAbstractClass();
        $logManager = $this->getMockBuilder(LogManager::class)
            ->setMethods(['getLogger'])
            ->disableOriginalConstructor()
            ->getMock();
        $logManager->method('getLogger')->willReturn($logger);
        GeneralUtility::setSingletonInstance(LogManager::class, $logManager);

        $valiatorResolver = $this->getMockBuilder(ValidatorResolver::class)
            ->setMethods(['resolveValidator'])
            ->disableOriginalConstructor()
            ->getMock();
        $valiatorResolver->method('resolveValidator')->willReturn(null);
        $subject = new OutletArgument('test', 'string');
        $subject->injectValidatorResolver($valiatorResolver);
        self::expectException(NoSuchValidatorException::class);
        $subject->addValidator('foobarbaz');

        GeneralUtility::resetSingletonInstances($singletons);
    }

    public function testCanInjectAndGetPropertyMappingConfiguration(): void
    {
        $propertyMappingConfiguration = new MvcPropertyMappingConfiguration();
        $subject = new OutletArgument('test', 'string');
        $subject->injectPropertyMappingConfiguration($propertyMappingConfiguration);
        self::assertSame($propertyMappingConfiguration, $subject->getPropertyMappingConfiguration());
    }
}
