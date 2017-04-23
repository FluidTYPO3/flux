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
use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;

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
        parent::setUp();
        $isSeven = version_compare(TYPO3_version, '8.0', '<') ;
        $methods = array('getTemplateVariableContainer', 'getViewHelperVariableContainer', 'getControllerContext');
        if (!$isSeven) {
            $methods[] = 'getVariableProvider';
        }
        $this->viewHelperVariableContainer = $this->getMockBuilder(
            $isSeven ? \TYPO3\CMS\Fluid\Core\ViewHelper\ViewHelperVariableContainer::class : \TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer::class
        )->setMethods(
            array('exists', 'get', 'add')
        )->getMock();
        $this->templateVariableContainer = $this->getMockBuilder(
            $isSeven ? TemplateVariableContainer::class : StandardVariableProvider::class
        )->setMethods(
            array('get', 'add')
        )->getMock();
        $this->controllerContext = $this->getMockBuilder(
            ControllerContext::class
        )->setMethods(
            array('getRequest')
        )->getMock();
        $this->controllerContext->expects($this->any())
            ->method('getRequest')
            ->willReturn(new Request());
        $this->renderingContext = new RenderingContext();
        $this->renderingContext->setControllerContext($this->controllerContext);
        if ($isSeven) {
            $this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
            $this->renderingContext->injectTemplateVariableContainer($this->templateVariableContainer);
        } else {
            $this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
            $this->renderingContext->setVariableProvider($this->templateVariableContainer);
        }
        /*
        $this->renderingContext = $this->getMockBuilder(
            RenderingContext::class
        )->setMethods(
            $methods
        )->getMock();

        $this->renderingContext->expects($this->any())
            ->method('getTemplateVariableContainer')
            ->willReturn($this->templateVariableContainer);
        if (!$isSeven) {
            $this->renderingContext->expects($this->any())
                ->method('getVariableProvider')
                ->willReturn($this->templateVariableContainer);
        }
        $this->renderingContext->expects($this->any())
            ->method('getViewHelperVariableContainer')
            ->willReturn($this->viewHelperVariableContainer);
        $this->renderingContext->expects($this->any())
            ->method('getControllerContext')
            ->willReturn($this->controllerContext);
        */
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
    public function canGetGridWhenItDoesNotExistButStorageDoes()
    {
        $this->viewHelperVariableContainer->expects($this->once())->method('get')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
            ->will($this->returnValue(array()));
        $instance = $this->createMockedInstanceForVariableContainerTests();
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
        $this->viewHelperVariableContainer->expects($this->any())->method('get')->will($this->returnValue($grids));
        $output = $this->callInaccessibleMethod($instance, 'getGrid', 'test');
        $this->assertSame($grid, $output);
    }

    /**
     * @test
     */
    public function canGetGridWhenItDoesNotExistAndStorageDoesNotExist()
    {
        $instance = $this->createMockedInstanceForVariableContainerTests();
        $this->viewHelperVariableContainer->expects($this->once())->method('get')
            ->with(AbstractFormViewHelper::SCOPE, AbstractFormViewHelper::SCOPE_VARIABLE_GRIDS)
            ->will($this->returnValue([Form\Container\Grid::create()]));
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
        if (method_exists($this->renderingContext, 'setViewHelperVariableContainer')) {
            $this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
        } else {
            $this->renderingContext->injectViewHelperVariableContainer($this->viewHelperVariableContainer);
        }
        $instance = $this->getMockBuilder($this->getViewHelperClassName())->setMethods($methods)->getMock();
        #$instance = $this->buildViewHelperInstance();
        $instance->setRenderingContext($this->renderingContext);
        #ObjectAccess::setProperty($instance, 'viewHelperVariableContainer', $this->viewHelperVariableContainer, true);
        #ObjectAccess::setProperty($instance, 'renderingContext', $this->renderingContext, true);
        return $instance;
    }
}
