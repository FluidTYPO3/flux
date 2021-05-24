<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use FluidTYPO3\Flux\Outlet\Pipe\StandardPipe;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class AbstractPipeViewHelper
 */
abstract class AbstractPipeViewHelper extends AbstractFormViewHelper
{
    use CompileWithRenderStatic;

    const DIRECTION_IN = 'in';
    const DIRECTION_OUT = 'out';

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument(
            'direction',
            'string',
            'Which endpoint to attach the Pipe to - either "in" or "out". See documentation about Outlets and Pipes',
            false,
            static::DIRECTION_OUT
        );
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {
        $form = static::getFormFromRenderingContext($renderingContext);
        $pipe = static::preparePipeInstance($renderingContext, $arguments);
        if ($arguments['direction'] === static::DIRECTION_IN) {
            $form->getOutlet()->addPipeIn($pipe);
        } else {
            $form->getOutlet()->addPipeOut($pipe);
        }
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param iterable $arguments
     * @param \Closure $renderChildrenClosure
     * @return PipeInterface
     */
    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        \Closure $renderChildrenClosure = null
    ) {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(StandardPipe::class);
    }
}
