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
 * Form group option ViewHelper
 *
 * Defines a logical group name for the Flux form, which can
 * then be read by extensions supporting Flux forms. How the
 * setting gets used is specific to the extension reading the
 * Flux form - please consult that documentation to learn
 * if/how the extension supports this option.
 */
class GroupViewHelper extends OptionViewHelper
{
    public static string $option = FormOption::GROUP;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Name of the group');
    }
}
