<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Add;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Add
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by arguments.
 *
 * DEPRECATED - use flux:field with custom "config" with renderMode and/or fieldWizard attributes
 * @deprecated Will be removed in Flux 10.0
 */
class AddViewHelper extends AbstractWizardViewHelper
{
    protected ?string $label = 'Add new record';

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument(
            'table',
            'string',
            'Table name that records are added to',
            true
        );
        $this->registerArgument(
            'pid',
            'mixed',
            'Storage page UID or (as is default) ###CURRENT_PID###',
            false,
            '###CURRENT_PID###'
        );
        $this->registerArgument('setValue', 'string', 'How to treat the record once created', false, 'prepend');
    }

    public static function getComponent(RenderingContextInterface $renderingContext, iterable $arguments): Add
    {
        /** @var array $arguments */
        /** @var Add $component */
        $component = static::getPreparedComponent(Add::class, $renderingContext, $arguments);
        $component->setTable((string) $arguments['table']);
        $component->setStoragePageUid((int) $arguments['pid']);
        $component->setSetValue($arguments['setValue']);
        return $component;
    }
}
