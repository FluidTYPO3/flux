<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class UserFunctionTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'function' => 'FluidTYPO3\Flux\UserFunction\NoFields->renderField',
        'arguments' => [1, 2],
    ];
}
