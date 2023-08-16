<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Form\Option;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\ViewHelpers\Form\OptionViewHelper;

/**
 * Form sorting option ViewHelper
 */
class SortingViewHelper extends OptionViewHelper
{
    public static string $option = FormOption::SORTING;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'value',
            'integer',
            'Sorting value of form (how this is applied depends on the implementation using the Form instance; ' .
            'see third-party feature documentation)'
        );
    }
}
