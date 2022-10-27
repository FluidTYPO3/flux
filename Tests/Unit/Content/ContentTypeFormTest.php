<?php
namespace FluidTYPO3\Flux\Tests\Unit\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\ContentTypeForm;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;

class ContentTypeFormTest extends AbstractTestCase
{
    public function testCreatesFormInstance(): void
    {
        $subject = ContentTypeForm::create();
        self::assertInstanceOf(
            Form::class,
            $subject
        );
    }

    public function testCreateSheetInFormInstance(): void
    {
        $subject = ContentTypeForm::create();
        $subject->createSheet('somesheet', 'Some Label');
        self::assertInstanceOf(
            Form\Container\Sheet::class,
            $subject->get('somesheet')
        );
    }
}
