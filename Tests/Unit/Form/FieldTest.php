<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field;
use FluidTYPO3\Flux\Form\Wizard\Add;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class FieldTest extends AbstractTestCase
{
    public function testBuildConfigurationReturnsExpectedArray(): void
    {
        $subject = new Field();
        self::assertSame(['default' => null], $subject->buildConfiguration());
    }

    public function testCreateThrowsUnexpectedValueExceptionWithoutType(): void
    {
        $settings = [
            'name' => 'test',
        ];
        self::expectExceptionCode(1667227598);
        $subject = Field::create($settings);
    }

    public function testCreateWillCreateFieldInstance(): void
    {
        $settings = [
            'type' => 'input',
            'name' => 'test',
        ];
        $subject = Field::create($settings);
        self::assertInstanceOf(Field::class, $subject);
    }

    public function testBuildCreatesExpectedTcaArray(): void
    {
        $settings = [
            'type' => 'input',
            'name' => 'test',
        ];
        $subject = Field::create($settings);
        $output = $subject->build();

        self::assertSame(
            [
                'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.fields.test',
                'exclude' => 0,
                'config' => [
                    'type' => 'input'
                ],
            ],
            $output
        );
    }

    public function testCanGetAndSetType(): void
    {
        $this->assertGetterAndSetterWorks('type', 'sometype', null, true);
    }

    public function testCanGetAndSetOnChange(): void
    {
        $this->assertGetterAndSetterWorks('onChange', 'someonchange', null, true);
    }
}
