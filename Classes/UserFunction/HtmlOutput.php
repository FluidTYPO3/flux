<?php
namespace FluidTYPO3\Flux\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormEngine;

/**
 * Renders HTML stored in a Fluid FlexForm HTML field
 */
class HtmlOutput
{

    /**
     * @param array $parameters
     * @param FormEngine $pObj
     * @return mixed
     */
    public function renderField(array &$parameters, &$pObj)
    {
        unset($pObj);
        return trim($parameters['parameters']['closure']($parameters));
    }
}
