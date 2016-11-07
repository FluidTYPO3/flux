<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\Outlet\ValidateViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * ValidateViewHelperTest
 */
class ValidateViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function testAddsArgumentsAsValidatorConfiguration()
    {
        $arguments = ['name' => 'test', 'type' => 'NotEmpty'];
        $renderingContext = $this->objectManager->get(RenderingContext::class);
        $viewHelperVariableContainer = $this->getMockBuilder(ViewHelperVariableContainer::class)->setMethods(['get', 'addOrUpdate'])->getMock();
        $viewHelperVariableContainer->expects($this->once())->method('get')->with($this->anything(), 'validators')->willReturn([]);
        $viewHelperVariableContainer->expects($this->once())->method('addOrUpdate')->with($this->anything(), 'validators', [$arguments]);
        $renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);
        ValidateViewHelper::renderStatic($arguments, function () { }, $renderingContext);
    }
}
