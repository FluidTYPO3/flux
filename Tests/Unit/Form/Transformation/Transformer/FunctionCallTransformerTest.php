<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\FunctionCallTransformer;
use FluidTYPO3\Flux\Tests\Mock\ExpressionBuilder;
use PHPUnit\Framework\TestCase;

class FunctionCallTransformerTest extends TestCase
{
    private FunctionCallTransformer $subject;

    public function setUp(): void
    {
        $this->subject = new FunctionCallTransformer();

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
            'supports arrow notation' => [true, 'foo->bar'],
            'does not support integer' => [false, 'integer'],
            'does not support string' => [false, 'string'],
        ];
    }

    public function testTransform(): void
    {
        $field = Form::create()->createField(Form\Field\Input::class, 'test');
        $output = $this->subject->transform($field, ExpressionBuilder::class . '->literal', 'foo');
        self::assertSame('literal', $output);
    }
}
