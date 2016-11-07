<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use FluidTYPO3\Flux\ViewHelpers\Outlet\ArgumentViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * ArgumentViewHelperTest
 */
class ArgumentViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @return void
     */
    protected function setUp()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] = [];
        parent::setUp();
    }

    /**
     * @test
     */
    public function testAddsArgumentToOutlet()
    {
        $outlet = $this->getMockBuilder(Outlet::class)->setMethods(['addArgument'])->getMock();
        $outlet->expects($this->once())->method('addArgument')->with($this->anything());
        $form = $this->getMockBuilder(Form::class)->setMethods(['getOutlet'])->getMock();
        $form->expects($this->once())->method('getOutlet')->willReturn($outlet);
        $renderingContext = $this->objectManager->get(RenderingContext::class);
        $renderingContext->getViewHelperVariableContainer()->add(AbstractFormViewHelper::SCOPE, 'form', $form);
        ArgumentViewHelper::renderStatic(['name' => 'test', 'type' => 'string'], function () { return null; }, $renderingContext);
    }
}
