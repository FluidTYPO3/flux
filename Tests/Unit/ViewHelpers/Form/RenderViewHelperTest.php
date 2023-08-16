<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Classes\AccessibleFormRenderViewHelper;
use FluidTYPO3\Flux\Tests\Unit\ViewHelpers\AbstractViewHelperTestCase;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RenderViewHelperTest extends AbstractViewHelperTestCase
{
    public function testRender(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'] = [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'] = [];
        $form = $this->getMockBuilder(Form::class)->addMethods(['dummy'])->getMock();
        $form->setOption(FormOption::RECORD, ['uid' => 123, 'test' => '']);
        $form->setOption(FormOption::RECORD_FIELD, 'test');
        $nodeFactory = $this->getMockBuilder(NodeFactory::class)->onlyMethods(['create'])->getMock();
        $nodeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->getMockBuilder(NodeInterface::class)->disableOriginalConstructor()->getMock());
        $instance = new AccessibleFormRenderViewHelper();
        GeneralUtility::addInstance(NodeFactory::class, $nodeFactory);
        $instance->setArguments(['form' => $form]);
        $instance->setRenderingContext($this->renderingContext);
        $instance->render();
    }
}
