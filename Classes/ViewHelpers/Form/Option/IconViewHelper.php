<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form\Option;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\ViewHelpers\Form\OptionViewHelper;

/**
 * ### Icon option
 *
 * Sets the `icon` option in the Flux form, which can then be read by
 * extensions using Flux forms. Consult the documentation of extensions
 * which use the `icon` setting to learn more about how icons are used.
 *
 * ``value`` needs to be the absolute path to the image file, e.g.
 * ``/typo3conf/ext/myext/Resources/Public/Icons/Element.svg``.
 *
 * #### Example
 *
 *     <flux:form.option.icon value="/typo3conf/ext/myext/Resources/Public/Icons/Element.svg"/>
 */
class IconViewHelper extends OptionViewHelper
{

    /**
     * @var string
     */
    public static $option = Form::OPTION_ICON;

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('value', 'string', 'Path and name of the icon file');
    }
}
