<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class AbstractFormViewHelperTest extends AbstractTestCase
{
    public function testGetComponentCreatesDefaultComponent(): void
    {
        self::assertInstanceOf(
            Form::class,
            AbstractFormViewHelper::getComponent(
                $this->getMockBuilder(RenderingContextInterface::class)->getMockForAbstractClass(),
                []
            )
        );
    }
}
