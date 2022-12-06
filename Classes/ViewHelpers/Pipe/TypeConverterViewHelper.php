<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\TypeConverterPipe;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Type Converter Outlet Pipe ViewHelper
 *
 * Adds a TypeConverterPipe to the Form's Outlet.
 */
class TypeConverterViewHelper extends AbstractPipeViewHelper
{
    public function initializeArguments(): void
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
        $this->registerArgument(
            'property',
            'string',
            'Optional property which needs to be converted in data. If empty, uses entire form data array as input.'
        );
    }

    protected static function preparePipeInstance(
        RenderingContextInterface $renderingContext,
        iterable $arguments,
        ?\Closure $renderChildrenClosure = null
    ): TypeConverterPipe {
        /** @var array $arguments */
        /** @var TypeConverterPipe $pipe */
        $pipe = GeneralUtility::makeInstance(TypeConverterPipe::class);
        /** @var TypeConverterInterface|class-string $converterInstanceOrClassName */
        $converterInstanceOrClassName = $arguments['typeConverter'];
        if (false === $converterInstanceOrClassName instanceof TypeConverterInterface) {
            $coreConverterTemplate = 'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\%sConverter';
            /** @var class-string $coreConverterFqn */
            $coreConverterFqn = sprintf($coreConverterTemplate, $converterInstanceOrClassName);
            if (class_exists($coreConverterFqn)) {
                $converterClassName = $coreConverterFqn;
            } else {
                $converterClassName = $converterInstanceOrClassName;
            }
            /** @var TypeConverterInterface $converter */
            $converter = GeneralUtility::makeInstance($converterClassName);
        } else {
            /** @var TypeConverterInterface $converter */
            $converter = $converterInstanceOrClassName;
        }

        $pipe->setPropertyName($arguments['property']);
        $pipe->setTypeConverter($converter);
        $pipe->setTargetType($arguments['targetType']);
        return $pipe;
    }
}
