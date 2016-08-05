<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractFormViewHelperTestCase
 */
abstract class AbstractFormViewHelperTestCase extends AbstractViewHelperTestCase
{

    /**
     * @return void
     */
    public function setUp()
    {
        $this->viewHelperVariableContainer = $this->getMockBuilder(
            'TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer'
        )->setMethods(
            array('exists', 'get', 'add')
        )->getMock();
        $this->templateVariableContainer = $this->getMockBuilder(
            'TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer')
        ->setMethods(
            array('exists', 'get', 'add')
        )->getMock();
        $this->renderingContext = $this->getMockBuilder(
            'TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface'
        )->setMethods(
            array('getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext')
        )->getMock();
        $this->controllerContext = $this->getMockBuilder(
            'TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext'
        )->setMethods(
            array('getRequest')
        )->getMock();
        $this->controllerContext->expects($this->any())
            ->method('getRequest')
            ->willReturn(new Request());

        $this->renderingContext->expects($this->any())
            ->method('getTemplateVariableContainer')
            ->willReturn($this->templateVariableContainer);
        $this->renderingContext->expects($this->any())
            ->method('getViewHelperVariableContainer')
            ->willReturn($this->viewHelperVariableContainer);
        $this->renderingContext->expects($this->any())
            ->method('getControllerContext')
            ->willReturn($this->controllerContext);
    }

    /**
     * @test
     */
    public function testGetExtensionNameReturnsExtensionNameArgumentIfSet()
    {
        $instance = $this->buildViewHelperInstance(array_merge($this->defaultArguments, array('extensionName' => 'foobar-ext')));
        $result = $this->callInaccessibleMethod($instance, 'getExtensionName');
        $this->assertEquals('foobar-ext', $result);
    }

    /**
     * @test
     */
    public function canCreateViewHelperInstanceAndRenderWithoutArguments()
    {
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        $this->assertInstanceOf($this->getViewHelperClassName(), $instance);
        $instance->render();
    }

    /**
     * @test
     */
    public function canGetFormInstanceFromTemplateVariables()
    {
        $form = Form::create();
        $instance = $this->createMockedInstanceForVariableContainerTests();
        $instance->setRenderingcontext($this->renderingContext);
        $this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(false));
        $this->templateVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(true));
        $this->templateVariableContainer->expects($this->any())->method('get')->will($this->returnValue($form));
        $output = $this->callInaccessibleMethod($instance, 'getForm');
        $this->assertSame($form, $output);
    }

    /**
     * @test
     */
    public function canGetContainerInstanceFromTemplateVariables()
    {
        $sheet = Form\Container\Sheet::create();
        $instance = $this->createMockedInstanceForVariableContainerTests();
        $this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(false));
        $this->templateVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(true));
        $this->templateVariableContainer->expects($this->any())->method('get')->will($this->returnValue($sheet));
        $output = $this->callInaccessibleMethod($instance, 'getContainer');
        $this->assertSame($sheet, $output);
    }

    /**
     * @test
     */
    public function canGetGridWhenItDoesNotExistButStorageDoes()
    {
        $form = Form::create();
        $instance = $this->createMockedInstanceForVariableContainerTests();
        $this->templateVariableContainer->expects($this->any())->method('exists')->willReturn(false);
        $this->viewHelperVariableContainer->expects($this->at(0))->method('exists')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
            ->will($this->returnValue(true));
        $this->viewHelperVariableContainer->expects($this->at(1))->method('get')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
            ->will($this->returnValue($form));
        $this->viewHelperVariableContainer->expects($this->at(2))->method('exists')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
            ->will($this->returnValue(true));
        $this->viewHelperVariableContainer->expects($this->at(3))->method('get')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
            ->will($this->returnValue(array()));
        $output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $output);
    }

    /**
     * @test
     */
    public function canGetGridWhenItExistInStorage()
    {
        $form = Form::create();
        $grid = Form\Container\Grid::create();
        $grid->setName('test');
        $grids = array(
            'test' => $grid
        );
        $instance = $this->createMockedInstanceForVariableContainerTests(array('getForm'));
        $instance->expects($this->any())->method('getForm')->will($this->returnValue($form));
        $this->viewHelperVariableContainer->expects($this->any())->method('exists')->will($this->returnValue(true));
        $this->viewHelperVariableContainer->expects($this->any())->method('get')->will($this->returnValue($grids));
        $output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
        $this->assertSame($grid, $output);
    }

    /**
     * @test
     */
    public function canGetGridWhenItDoesNotExistAndStorageDoesNotExist()
    {
        $form = Form::create();
        $instance = $this->createMockedInstanceForVariableContainerTests();
        $this->viewHelperVariableContainer->expects($this->at(0))->method('exists')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
            ->will($this->returnValue(true));
        $this->viewHelperVariableContainer->expects($this->at(1))->method('get')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_FORM)
            ->will($this->returnValue($form));
        $this->viewHelperVariableContainer->expects($this->at(2))->method('exists')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
            ->will($this->returnValue(false));
        $output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
        $this->assertInstanceOf('FluidTYPO3\Flux\Form\Container\Grid', $output);
    }

    /**
     * @param array $methods
     * @return object
     */
    protected function createMockedInstanceForVariableContainerTests($methods = array())
    {
        if (true === empty($methods)) {
            $methods[] = 'dummy';
        }
        $instance = $this->getMockBuilder($this->getViewHelperClassName())->setMethods($methods)->getMock();
        $instance->setRenderingContext($this->renderingContext);
        return $instance;
    }
}
