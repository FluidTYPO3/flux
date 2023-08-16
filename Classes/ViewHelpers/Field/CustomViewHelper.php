<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Custom;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Custom FlexForm field ViewHelper
 */
class CustomViewHelper extends UserFuncViewHelper
{
    const DEFAULT_USERFUNCTION = 'FluidTYPO3\\Flux\\UserFunction\\HtmlOutput->renderField';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->overrideArgument(
            'userFunc',
            'string',
            'User function to render the Closure built by this ViewHelper',
            false,
            static::DEFAULT_USERFUNCTION
        );
    }

    protected function callRenderMethod(): string
    {
        $container = static::getContainerFromRenderingContext($this->renderingContext);
        $component = static::getComponent(
            $this->renderingContext,
            $this->arguments,
            $this->buildRenderChildrenClosure()
        );
        // rendering child nodes with Form's last sheet as active container
        static::setContainerInRenderingContext($this->renderingContext, $component);
        $this->renderChildren();
        static::setContainerInRenderingContext($this->renderingContext, $container);
        return '';
    }

    public static function getComponent(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        ?\Closure $renderChildrenClosure = null
    ): Custom {
        /** @var \Closure $renderChildrenClosure */
        /** @var Custom $component */
        $component = parent::getPreparedComponent(Custom::class, $renderingContext, $arguments);
        $component->setClosure($renderChildrenClosure);
        return $component;
    }

    protected static function buildClosure(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        \Closure $renderChildrenClosure
    ): \Closure {
        $container = $renderingContext->getVariableProvider();
        $closure = function ($parameters) use ($container, $renderChildrenClosure) {
            $backupParameters = null;
            $backupParameters = null;
            if ($container->exists('parameters') === true) {
                $backupParameters = $container->get('parameters');
                $container->remove('parameters');
            }
            $container->add('parameters', $parameters);
            $content = $renderChildrenClosure();
            $container->remove('parameters');
            if (null !== $backupParameters) {
                $container->add('parameters', $backupParameters);
            }
            return $content;
        };
        return $closure;
    }
}
