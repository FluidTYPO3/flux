<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\IntegerTransformer;
use PHPUnit\Framework\TestCase;

class IntegerTransformerTest extends TestCase
{
    private IntegerTransformer $subject;

    public function setUp(): void
    {
        $this->subject = new IntegerTransformer();

        parent::setUp();
    }

    public function testGetPriority(): void
    {
        self::assertSame(0, $this->subject->getPriority());
    }

    /**
     * @dataProvider getCanTransformToTypeTestValues
     */
    public function testCanTransformToType(bool $expected, string $type): void
    {
        self::assertSame($expected, $this->subject->canTransformToType($type));
    }

    public function getCanTransformToTypeTestValues(): array
    {
        return [
            'supports int' => [true, 'int'],
            'supports integer' => [true, 'integer'],
            'does not support float' => [false, 'float'],
            'does not support string' => [false, 'string'],
        ];
    }

    /**
     * @dataProvider getTransformTestValues
     * @param mixed $input
     */
    public function testTransform(int $expected, $input): void
    {
        $field = Form::create()->createField(Form\Field\Input::class, 'test');
        $output = $this->subject->transform($field, 'float', $input);
        self::assertSame($expected, $output);
    }

    public function getTransformTestValues(): array
    {
        return [
            'already integer' => [3, 3],
            'string' => [3, '3'],
            'float' => [3, 3.1],
        ];
    }
}
