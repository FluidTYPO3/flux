<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\File;

class FileTest extends AbstractFieldTest
{
    protected array $chainProperties = [
        'name' => 'test',
        'label' => 'Test field',
        'enabled' => true,
        'maxSize' => 135153542,
        'allowed' => 'jpg,gif',
        'disallowed' => 'doc,docx',
        'uploadFolder' => '',
        'showThumbnails' => true
    ];

    public function testBuildWithUseFalRelation(): void
    {
        $subject = File::create(['name' => 'test', 'useFalRelation' => true]);
        $output = $subject->build();
        $expected = [
            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.fields.test',
            'exclude' => 0,
            'config' => [
                'type' => 'group',
                'size' => 1,
                'minitems' => 0,
                'multiple' => false,
                'allowed' => 'sys_file',
                'show_thumbs' => false,
                'internal_type' => 'db',
                'appearance' => [
                    'elementBrowserAllowed' => '*',
                    'elementBrowserType' => 'file',
                ],
            ],
        ];
        self::assertSame($expected, $output);
    }
}
