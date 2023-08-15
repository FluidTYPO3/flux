<?php
namespace FluidTYPO3\Flux\Tests\Unit\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Form\Container\SectionObject;
use FluidTYPO3\Flux\Form\Field\Input;

class SectionTest extends AbstractContainerTest
{
    /**
     * @test
     */
    public function canCreateFromDefinitionWithObjects(): void
    {
        $definition = [
            'name' => 'test',
            'label' => 'Test section',
            'objects' => [
                'object1' => [
                    'label' => 'Test object',
                    'fields' => [
                        'foo' => [
                            'type' => Input::class,
                            'label' => 'Foo input',
                        ],
                    ],
                ],
            ],
        ];
        $section = Section::create($definition);
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Section', $section);
    }

    public function testCreateSectionWithContentContainer(): void
    {
        $expected = [
            'type' => 'array',
            'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.sections.test',
            'section' => '1',
            'el' => [
                'columns' => [
                    'title' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.test.objects.columns',
                    'type' => 'array',
                    'el' => [
                        'colPos' => [
                            'label' => 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.' .
                                'test.objects.columns.colPos',
                            'exclude' => 0,
                            'config' => [
                                'type' => 'user',
                                'renderType' => 'fluxColumnPosition',
                            ],
                        ],
                        'label' => [
                            'label' => 'Content area name/label',
                            'exclude' => 0,
                            'config' => [
                                'type' => 'input',
                                'size' => 32,
                                'eval' => 'trim',
                            ],
                        ],
                        'colspan' => [
                            'label' => 'Width of column',
                            'exclude' => 0,
                            'config' => [
                                'type' => 'select',
                                'size' => 1,
                                'minitems' => 0,
                                'multiple' => false,
                                'renderType' => 'selectSingle',
                                'items' => [
                                    [1, 1],
                                    [2, 2],
                                    [3, 3],
                                    [4, 4],
                                    [5, 5],
                                    [6, 6],
                                    [7, 7],
                                    [8, 8],
                                    [9, 9],
                                    [10, 10],
                                    [11, 11],
                                    [12, 12],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $subject = Section::create(['name' => 'test']);
        $subject->setGridMode(Section::GRID_MODE_COLUMNS);
        $container = $subject->createContainer(SectionObject::class, 'columns');
        $container->setContentContainer(true);

        self::assertTrue($container->isContentContainer(), 'Container is not marked as content container');
        self::assertSame($container, $subject->getContentContainer(), 'Subject does not return content container');
        self::assertSame($expected, $subject->build(), 'Subject build() did not create expected value');
    }

    public function testGetContentContainerReturnsNullWithoutContentContainer(): void
    {
        $subject = Section::create(['name' => 'test']);
        self::assertNull($subject->getContentContainer());
    }
}
