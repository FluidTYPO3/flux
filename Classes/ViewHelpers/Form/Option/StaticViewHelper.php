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
 * Form static caching option ViewHelper
 *
 * Use this only when your Flux form is 100% static and
 * will work when cached.
 */
class StaticViewHelper extends OptionViewHelper
{
    public static string $option = FormOption::STATIC;

    public function initializeArguments(): void
    {
        $this->registerArgument(
            'value',
            'boolean',
            'Configures caching of the DS resulting from the form. Default when used is TRUE which enables caching',
            false,
            true
        );
    }
}
