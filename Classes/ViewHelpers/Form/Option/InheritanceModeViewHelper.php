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
 * ### Inheritance mode option
 *
 * Control how this Form will handle inheritance (page context only).
 * There are two possible values of this option:
 *
 * - restricted
 * - unrestricted
 *
 * Note that the default (the mode which is used if you do NOT specify
 * the mode with this ViewHelper/option) is defined by the Flux extension
 * configuration. If you do not change the extension configuration then
 * the default behavior is "restricted". Any template that wants to use
 * a mode other than the default *MUST* specify the mode with this option.
 *
 * When the option is set to "restricted" either by this ViewHelper or
 * by extension configuration, the inheritance behavior matches the
 * Flux behavior pre version 10.1.x, meaning that inheritance will only
 * happen if the parent (page) has selected the same Form (layout) as
 * the current page. As soon as a different Form is encountered in a
 * parent, the inheritance stops. In short: inheritance only works for
 * identical Forms.
 *
 * Alternatively, when the option is set to "unrestricted", the above
 * constraint is removed and inheritance can happen for Forms which are
 * NOT the same.
 *
 * This makes sense to use if you have different page templates which
 * use the same values (for example a shared set of fields) and you want
 * child pages to be able to inherit these values from parents even if
 * the child page has selected a different page layout.
 *
 * #### Example
 *
 *     <flux:form.option.inheritanceMode value="unrestricted" />
 *     (which is the same as:)
 *     <flux:form.option.inheritanceMode>unrestricted</flux:form.option.inheritanceMode>
 *
 * Or:
 *
 *     <flux:form.option.inheritanceMode value="restricted" />
 *     (which is the same as:)
 *     <flux:form.option.inheritanceMode>restricted</flux:form.option.inheritanceMode>
 */
class InheritanceModeViewHelper extends OptionViewHelper
{
    public static string $option = FormOption::INHERITANCE_MODE;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'Mode of inheritance, either "restricted" or "unrestricted".');
    }
}
