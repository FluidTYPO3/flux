<?php
namespace FluidTYPO3\Flux\Tests\Unit\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\OutletArgument;
use FluidTYPO3\Flux\Outlet\Pipe\FlashMessagePipe;
use FluidTYPO3\Flux\Outlet\Pipe\StandardPipe;
use FluidTYPO3\Flux\Outlet\Pipe\ViewAwarePipeInterface;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\CMS\Fluid\View\TemplateView;

/**
 * AbstractOutletTestCase
 */
abstract class AbstractOutletTestCase extends AbstractTestCase
{
    public function testCreateFromSettingsArray(): void
    {
        $settings = [
            'pipesIn' => [
                [
                    'type' => StandardPipe::class,
                    'foo' => 'foo',
                ]
            ],
            'pipesOut' => [
                [
                    'type' => StandardPipe::class,
                    'foo' => 'bar',
                ],
                [
                    'type' => FlashMessagePipe::class,
                    'foo' => 'baz',
                    'title' => 'test',
                ]
            ],
        ];
        $outletClass = $this->createInstanceClassName();
        $instance = $outletClass::create($settings);

        self::assertCount(1, $instance->getPipesIn(), 'Pipes-in count does not match expected count');
        self::assertCount(2, $instance->getPipesOut(), 'Pipes-out count does not match expected count');
    }

    public function testGetAndSetArguments(): void
    {
        $arguments = [
            new OutletArgument('test', 'string'),
        ];
        $subject = $this->createInstance();
        $this->assertGetterAndSetterWorks('arguments', $arguments, $arguments, true);
    }

    public function testIsValidReturnsTrueBeforeValidation(): void
    {
        $subject = $this->createInstance();
        self::assertTrue($subject->isValid());
    }

    public function testIsValidReturnsFalseIfArgumentValidationFailed(): void
    {
        $validatorResolver = $this->getMockBuilder(ValidatorResolver::class)->disableOriginalConstructor()->getMock();
        $validatorResolver->method('createValidator')->willReturn(new NotEmptyValidator());

        $argument = $this->getMockBuilder(OutletArgument::class)
            ->setMethods(['getValue', 'setValue', 'isValid', 'getValidationResults'])
            ->setConstructorArgs(['test', 'string'])
            ->getMock();
        $argument->method('getValue')->willReturn(null);
        $argument->method('isValid')->willReturn(false);
        $argument->method('getValidationResults')->willReturn(new Result());
        $argument->injectValidatorResolver($validatorResolver);
        $argument->addValidator(NotEmptyValidator::class);

        $subject = $this->createInstance();
        $subject->addArgument($argument);

        $subject->validate(['test' => null]);
        self::assertTrue($subject->isValid());
    }

    /**
     * @test
     */
    public function canGetAndSetEnabled()
    {
        $this->assertGetterAndSetterWorks('enabled', false, false, true);
    }

    /**
     * @test
     */
    public function canGetAndSetPipesIn()
    {
        $pipes = array(
            new StandardPipe()
        );
        $this->assertGetterAndSetterWorks('pipesIn', $pipes, $pipes, true);
    }

    /**
     * @test
     */
    public function canAddAndRetrievePipeIn()
    {
        $instance = $this->createInstance();
        $pipe = new StandardPipe();
        $instance->addPipeIn($pipe);
        $this->assertContains($pipe, $instance->getPipesIn());
    }

    /**
     * @test
     */
    public function canGetAndSetPipesOut()
    {
        $pipes = array(
            new StandardPipe()
        );
        $this->assertGetterAndSetterWorks('pipesOut', $pipes, $pipes, true);
    }

    /**
     * @test
     */
    public function canAddAndRetrievePipeOut()
    {
        $instance = $this->createInstance();
        $pipe = new StandardPipe();
        $instance->addPipeOut($pipe);
        $this->assertContains($pipe, $instance->getPipesOut());
    }

    /**
     * @test
     */
    public function canGetAndSetView()
    {
        $view = $this->getMockBuilder(TemplateView::class)->disableOriginalConstructor()->getMock();
        $this->assertGetterAndSetterWorks('view', $view, $view, true);
    }

    public function testFillTransfersViewToPipesIn(): void
    {
        $view = $this->getMockBuilder(TemplateView::class)->disableOriginalConstructor()->getMock();
        $subject = $this->createInstance();
        $subject->setView($view);
        $pipe = $this->getMockBuilder(ViewAwarePipeInterface::class)->getMockForAbstractClass();
        $pipe->expects(self::once())->method('setView')->with($view);
        $subject->addPipeIn($pipe);
        $subject->fill(['foo' => 'bar']);
    }

    public function testProduceTransfersViewToPipesOut(): void
    {
        $view = $this->getMockBuilder(TemplateView::class)->disableOriginalConstructor()->getMock();
        $subject = $this->createInstance();
        $subject->setView($view);
        $pipe = $this->getMockBuilder(ViewAwarePipeInterface::class)->getMockForAbstractClass();
        $pipe->expects(self::once())->method('setView')->with($view);
        $subject->addPipeOut($pipe);
        $subject->produce();
    }

    /**
     * @test
     */
    public function fillsWithDataAndConductsUsingPipes()
    {
        $instance = $this->createInstance();
        $data = array('test');
        $pipe = $this->getMockBuilder('FluidTYPO3\Flux\Outlet\Pipe\StandardPipe')->setMethods(array('conduct'))->getMock();
        $pipe->expects($this->exactly(2))->method('conduct')->with($data)->will($this->returnValue($data));
        $pipes = array(
            $pipe
        );
        $output = $instance->setPipesIn($pipes)->setPipesOut($pipes)->fill($data)->produce();
        $this->assertSame($data, $output);
    }
}
