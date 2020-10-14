<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\OptionCarryingInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Form option ViewHelper
 */
class OptionViewHelper extends AbstractFormViewHelper
{
    use CompileWithContentArgumentAndRenderStatic;

    /**
     * @var string
     */
    public static $option;

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'Option value');
        $this->registerArgument('name', 'string', 'Name of the option to be set', true);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return void
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $option = isset($arguments['name']) ? $arguments['name'] : static::$option;
        $container = static::getContainerFromRenderingContext($renderingContext);
        $value = $renderChildrenClosure();
        if ($container instanceof OptionCarryingInterface) {
            $container->setOption($option, $value);
            return;
        }
        throw new \UnexpectedValueException(
            'flux:form.option cannot be used as child element of ' . get_class($container) . ' (this class does not support options). '
            . 'Please correct this in your template file(s). The option had name="' . $option . '" and value="' . $value . '"',
            1602693000
        );
    }
}
