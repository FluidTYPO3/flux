<?php
namespace FluidTYPO3\Flux\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;

/**
 * OutletViewHelperTest
 */
class OutletViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function testCanDisableOutlet()
    {
        $outlet = $this->getMockBuilder(Outlet::class)->setMethods(['setEnabled'])->getMock();
        $outlet->expects($this->once())->method('setEnabled')->with(false);
        $form = $this->getMockBuilder(Form::class)->setMethods(['getOutlet'])->getMock();
        $form->expects($this->once())->method('getOutlet')->willReturn($outlet);
        $renderingContext = $this->objectManager->get(RenderingContext::class);
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(OutletViewHelper::class, 'provider', null);
        $renderingContext->getViewHelperVariableContainer()->addOrUpdate(OutletViewHelper::class, 'record', []);
        $renderingContext->getViewHelperVariableContainer()->add(AbstractFormViewHelper::SCOPE, 'form', $form);
        OutletViewHelper::renderStatic(['enabled' => false], function () { return null; }, $renderingContext);
    }
}
