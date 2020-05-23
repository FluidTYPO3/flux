<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Mvc\Controller\ControllerContext;
use TYPO3\CMS\Extbase\Mvc\Web\Request;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContext;
use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;

/**
 * AbstractFormViewHelperTestCase
 */
abstract class AbstractFormViewHelperTestCase extends AbstractViewHelperTestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->viewHelperVariableContainer = $this->getMockBuilder(
            ViewHelperVariableContainer::class
        )->setMethods(
            array('exists', 'get', 'add')
        )->getMock();
        $this->templateVariableContainer = $this->getMockBuilder(
            StandardVariableProvider::class
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
        $this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
        $this->renderingContext->setVariableProvider($this->templateVariableContainer);
    }

    /**
     * @test
     */
    public function canCreateViewHelperInstanceAndRenderWithoutArguments()
    {
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        $this->assertInstanceOf($this->getViewHelperClassName(), $instance);
        if (method_exists($instance, 'initializeArgumentsAndRender')) {
            $instance->initializeArgumentsAndRender();
        } elseif (method_exists($instance, 'render')) {
            $instance->render();
        } elseif (method_exists($instance, 'evaluate')) {
            $instance->evaluate(new RenderingContext());
        }
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
        $instance->setRenderingContext($this->renderingContext);
        return $instance;
    }
}
