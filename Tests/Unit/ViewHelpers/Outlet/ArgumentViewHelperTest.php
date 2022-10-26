<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\OutletArgument;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleArgumentViewHelper;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * ArgumentViewHelperTest
 */
class ArgumentViewHelperTest extends AbstractViewHelperTestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters'] = [];
        $objectManager = $this->getMockBuilder(ObjectManagerInterface::class)->getMockForAbstractClass();
        $objectManager->method('get')->willReturn(new OutletArgument('test', 'string'));

        AccessibleArgumentViewHelper::setObjectManager($objectManager);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        AccessibleArgumentViewHelper::setObjectManager(null);
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
        $this->viewHelperVariableContainer->add(AbstractFormViewHelper::SCOPE, 'form', $form);

        AccessibleArgumentViewHelper::renderStatic(['name' => 'test', 'type' => 'string'], function () { return null; }, $this->renderingContext);
    }
}
