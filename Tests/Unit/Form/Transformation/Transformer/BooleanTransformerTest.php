<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Transformation\Transformer;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Transformation\Transformer\BooleanTransformer;
use PHPUnit\Framework\TestCase;

class BooleanTransformerTest extends TestCase
{
    private BooleanTransformer $subject;

    public function setUp(): void
    {
        $this->subject = new BooleanTransformer();

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
            'supports bool' => [true, 'bool'],
            'supports boolean' => [true, 'boolean'],
            'does not support array' => [false, 'array'],
            'does not support string' => [false, 'string'],
        ];
    }

    /**
     * @dataProvider getTransformTestValues
     * @param mixed $input
     */
    public function testTransform(bool $expected, $input): void
    {
        $field = Form::create()->createField(Form\Field\Input::class, 'test');
        $output = $this->subject->transform($field, 'bool', $input);
        self::assertSame($expected, $output);
    }

    public function getTransformTestValues(): array
    {
        return [
            'already boolean' => [true, true],
            'string true-ish' => [true, '1'],
            'string false-ish' => [false, '0'],
        ];
    }
}
