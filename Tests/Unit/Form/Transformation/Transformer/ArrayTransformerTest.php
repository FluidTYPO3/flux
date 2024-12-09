<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\ArrayTransformer;
use PHPUnit\Framework\TestCase;

class ArrayTransformerTest extends TestCase
{
    private ArrayTransformer $subject;

    public function setUp(): void
    {
        $this->subject = new ArrayTransformer();

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
            'supports array' => [true, 'array'],
            'does not support bool' => [false, 'bool'],
            'does not support string' => [false, 'string'],
        ];
    }

    /**
     * @dataProvider getTransformTestValues
     * @param mixed $input
     */
    public function testTransform(array $expected, $input): void
    {
        $field = Form::create()->createField(Form\Field\Input::class, 'test');
        $output = $this->subject->transform($field, 'array', $input);
        self::assertSame($expected, $output);
    }

    public function getTransformTestValues(): array
    {
        return [
            'already array' => [['foo'], ['foo']],
            'string CSV' => [['foo', 'bar'], 'foo,bar'],
            'integer' => [[1], 1],
            'iterator' => [['foo', 'bar'], new \ArrayIterator(['foo', 'bar'])],
        ];
    }
}
