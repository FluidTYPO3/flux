<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;

/**
 * RenderViewHelperTest
 */
class RenderViewHelperTest extends AbstractViewHelperTestCase
{

    /**
     * @test
     */
    public function testRender()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'] = array();
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'] = array();
        $form = Form::create();
        $nodeFactory = $this->getMockBuilder(NodeFactory::class)->setMethods(array('create'))->getMock();
        $nodeFactory->expects($this->once())->method('create')->willReturn($this->getMockBuilder(NodeInterface::class)->getMock());
        $instance = $this->getMockBuilder($this->createInstanceClassName())->setMethods(array('getNodeFactory'))->getMock();
        $instance->expects($this->once())->method('getNodeFactory')->willReturn($nodeFactory);
        $instance->render($form);
    }
}
