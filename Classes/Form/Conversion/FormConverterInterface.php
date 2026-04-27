<?php
namespace FluidTYPO3\Flux\Form\Conversion;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

interface FormConverterInterface
{
    /**
     * @return mixed
     */
    public function convertFormAndGrid(Form $form, Form\Container\Grid $grid, array $configuration);
}
