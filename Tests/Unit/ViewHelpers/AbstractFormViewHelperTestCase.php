<?php
namespace FluidTYPO3\Flux\Tests\Unit\ViewHelpers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

abstract class AbstractFormViewHelperTestCase extends AbstractViewHelperTestCase
{
    /**
     * @test
     */
    public function canCreateViewHelperInstanceAndRenderWithoutArguments(): void
    {
        /** @var ViewHelperInterface $instance */
        $instance = $this->buildViewHelperInstance($this->defaultArguments);
        $this->renderingContext->getViewHelperInvoker()->invoke(
            $instance,
            $this->defaultArguments,
            $this->renderingContext
        );
        self::assertSame(true, true);
    }

    protected function createMockedInstanceForVariableContainerTests(array $methods = []): AbstractFormViewHelper
    {
        if (true === empty($methods)) {
            $methods[] = 'dummy';
        }
        $this->renderingContext->setViewHelperVariableContainer($this->viewHelperVariableContainer);
        $instance = $this->getMockBuilder($this->getViewHelperClassName())->onlyMethods($methods)->getMock();
        $instance->setRenderingContext($this->renderingContext);
        return $instance;
    }
}
