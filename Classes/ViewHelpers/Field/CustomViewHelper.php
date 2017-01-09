<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Custom;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Custom FlexForm field ViewHelper
 */
class CustomViewHelper extends UserFuncViewHelper
{

    const DEFAULT_USERFUNCTION = 'FluidTYPO3\\Flux\\UserFunction\\HtmlOutput->renderField';

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->overrideArgument(
            'userFunc',
            'string',
            'User function to render the Closure built by this ViewHelper',
            false,
            self::DEFAULT_USERFUNCTION
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return Custom
     */
    public static function getComponent(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure
    ) {
        /** @var Custom $component */
        $component = parent::getPreparedComponent('Custom', $renderingContext, $arguments);
        $closure = static::buildClosure($renderingContext, $arguments, $renderChildrenClosure);
        $component->setClosure($closure);
        return $component;
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return \Closure
     */
    protected static function buildClosure(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure
    ) {
        $container = $renderingContext->getTemplateVariableContainer();
        $closure = function ($parameters) use ($container, $renderingContext, $renderChildrenClosure) {
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
