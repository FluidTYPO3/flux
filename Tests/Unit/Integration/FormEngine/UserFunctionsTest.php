<?php
namespace FluidTYPO3\Flux\Tests\Unit\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Integration\FormEngine\UserFunctions;
use FluidTYPO3\Flux\Provider\Interfaces\FormProviderInterface;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class UserFunctionsTest extends AbstractTestCase
{
    private ?ProviderResolver $providerResolver;

    protected function setUp(): void
    {
        $this->providerResolver = $this->getMockBuilder(ProviderResolver::class)
            ->setMethods(['resolvePrimaryConfigurationProvider'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->singletonInstances[ProviderResolver::class] = $this->providerResolver;

        parent::setUp();
    }

    /**
     * @dataProvider getUserFunctionTestValues
     */
    public function testCanCallMethodAndReceiveOutput(string $method, array $parameters, bool $expectsNull): void
    {
        $reference = $this->getMockBuilder(UserFunctions::class)->getMock();
        $subject = $this->getMockBuilder(UserFunctions::class)->setMethods(['translate'])->getMock();
        $subject->method('translate')->willReturnArgument(0);
        $output = $subject->$method($parameters, $reference);
        if ($expectsNull) {
            $this->assertNull($output);
        } else {
            $this->assertNotEmpty($output);
        }
    }

    public function getUserFunctionTestValues(): array
    {
        return [
            'HTML output field' => [
                'renderHtmlOutputField',
                ['parameters' => ['closure' => function () {
                    return 'test';
                }]],
                false
            ],
        ];
    }

    public function testFluxFormFieldDisplayConditionWithProvider(): void
    {
        $provider = $this->getMockBuilder(FormProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn(
            Form::create(['fields' => ['test' => ['type' => Form\Field\Input::class]]])
        );
        $this->assertFluxFormFieldDisplayConditionReturnValue(true, $provider);
    }

    public function testFluxFormFieldDisplayConditionWithProviderWithFormWithoutFields(): void
    {
        $provider = $this->getMockBuilder(FormProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn(Form::create());
        $this->assertFluxFormFieldDisplayConditionReturnValue(false, $provider);
    }

    public function testFluxFormFieldDisplayConditionWithProviderWithoutForm(): void
    {
        $provider = $this->getMockBuilder(FormProviderInterface::class)->getMockForAbstractClass();
        $provider->method('getForm')->willReturn(null);
        $this->assertFluxFormFieldDisplayConditionReturnValue(false, $provider);
    }

    public function testFluxFormFieldDisplayConditionWithoutProvider(): void
    {
        $this->assertFluxFormFieldDisplayConditionReturnValue(true, null);
    }

    private function assertFluxFormFieldDisplayConditionReturnValue(
        bool $expected,
        ?FormProviderInterface $provider
    ): void {
        $this->providerResolver->method('resolvePrimaryConfigurationProvider')->willReturn($provider);
        $subject = new UserFunctions();
        $parameters = [
            'record' => [],
            'conditionParameters' => [
                'tt_content',
                'test',
            ],
        ];
        $parentObject = new \stdClass();
        self::assertSame($expected, $subject->fluxFormFieldDisplayCondition($parameters, $parentObject));
    }

    public function testRenderColumnPositionFieldWithExistingColPosValue(): void
    {
        $parameters = [
            'parameterArray' => ['itemFormElValue' => '2', 'itemFormElName' => 'colPos'], 'databaseRow' => []
        ];
        $subject = new UserFunctions();
        $output = $subject->renderColumnPositionField($parameters);
        self::assertStringContainsString('name="colPos"', $output);
        self::assertStringContainsString('value="2"', $output);
    }

    public function testRenderColumnPositionFieldWithoutExistingColPosValue(): void
    {
        $parameters = [
            'parameterArray' => ['itemFormElValue' => '', 'itemFormElName' => 'colPos'],
            'databaseRow' => ['uid' => 'NEW123']
        ];
        $subject = $this->getMockBuilder(UserFunctions::class)
            ->onlyMethods(['determineTakenColumnPositionsWithinParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $subject->method('determineTakenColumnPositionsWithinParent')->willReturn([1, 2]);
        $output = $subject->renderColumnPositionField($parameters);
        self::assertStringContainsString('name="colPos"', $output);
        self::assertStringContainsString('data-min-value="0"', $output);
        self::assertStringContainsString('data-max-value="99"', $output);
        self::assertStringContainsString('data-taken-values="1,2"', $output);
    }
}
