<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class InlineViewHelperTest extends AbstractFieldViewHelperTestCase
{
    protected array $defaultArguments = [
        'name' => 'test',
        'table' => 'tt_content',
        'enabledControls' => [
            'new' => true,
            'hide' => true
        ],
        'foreignTypes' => [
            0 => [
                'showitem' => 'a,b,c'
            ]
        ]
    ];
}
