<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentGridForm;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Form\Container\SectionObject;
use FluidTYPO3\Flux\Form\Field\ColumnPosition;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ContentGridFormTest extends AbstractTestCase
{
    public function testCreatesFormWithGridModeField(): void
    {
        $subject = ContentGridForm::create();
        static::assertInstanceOf(
            Select::class,
            $subject->get('grid')->get('gridMode')
        );
    }

    public function testCreatesFormWithColumnsSection(): void
    {
        $subject = ContentGridForm::create();
        static::assertInstanceOf(
            Section::class,
            $subject->get('grid')->get('columns')
        );
    }

    public function testCreatesFormWithColumnObjectInColumnsSection(): void
    {
        $subject = ContentGridForm::create();
        static::assertInstanceOf(
            SectionObject::class,
            $subject->get('grid')->get('columns')->get('column')
        );
    }

    public function testCreatesFormWithColumnPositionFieldInColumnObject(): void
    {
        $subject = ContentGridForm::create();
        static::assertInstanceOf(
            ColumnPosition::class,
            $subject->get('grid')->get('columns')->get('column')->get('colPos')
        );
    }
}
