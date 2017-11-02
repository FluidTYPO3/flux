<?php
namespace FluidTYPO3\Flux\Provider\Interfaces;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * Interface FormProviderInterface
 *
 * Contract for Providers which are capable of returning
 * Form instances.
 */
interface FormProviderInterface
{
    /**
     * Returns an instance of \FluidTYPO3\Flux\Form as required by this record.
     *
     * @param array $row
     * @return Form|null
     */
    public function getForm(array $row);

    /**
     * @param Form $form
     */
    public function setForm(Form $form);
}
