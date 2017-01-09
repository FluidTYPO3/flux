<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * WizardInterface
 */
interface WizardInterface extends FormInterface
{

    /**
     * @return array
     */
    public function buildConfiguration();

    /**
     * @param boolean $hideParent
     * @return WizardInterface
     */
    public function setHideParent($hideParent);

    /**
     * @return boolean
     */
    public function getHideParent();
}
