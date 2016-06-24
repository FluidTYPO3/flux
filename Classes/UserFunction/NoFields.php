<?php
namespace FluidTYPO3\Flux\UserFunction;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Renders nothing in case no fields are defined, label is used for feedback
 */
class NoFields
{

    /**
     * @param array $parameters Not used
     * @param object $pObj Not used
     * @return string
     */
    public function renderField(&$parameters, &$pObj)
    {
        unset($pObj, $parameters);
        return LocalizationUtility::translate('user.no_fields', 'Flux');
    }
}
