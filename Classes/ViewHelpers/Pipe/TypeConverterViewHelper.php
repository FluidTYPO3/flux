<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use FluidTYPO3\Flux\Outlet\Pipe\TypeConverterPipe;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Type Converter Outlet Pipe ViewHelper
 *
 * Adds a TypeConverterPipe to the Form's Outlet.
 */
class TypeConverterViewHelper extends AbstractPipeViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('targetType', 'string', 'Target type (class name, integer, array, etc.)', true);
        $this->registerArgument(
            'typeConverter',
            'mixed',
            'Class or instance of type converter. Can be a short name of a system type converter, minus ' .
            '"Converter" suffix, e.g. PersistentObject, Array etc.',
            true
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @return PipeInterface
     */
    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        array $arguments,
        \Closure $renderChildrenClosure = null
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        /** @var TypeConverterPipe $pipe */
        $pipe = $objectManager->get(TypeConverterPipe::class);
        $converter = $arguments['typeConverter'];
        if (false === $converter instanceof TypeConverterInterface) {
            if (false === class_exists($converter)) {
                $converter = 'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\' . $converter . 'Converter';
            }
            $converter = $objectManager->get($converter);
        }
        $pipe->setTypeConverter($converter);
        $pipe->setTargetType($arguments['targetType']);
        return $pipe;
    }
}
