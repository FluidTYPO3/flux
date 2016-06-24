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
 * Renders nothing in case no template is selected
 */
class NoSelection
{

    /**
     * @param array $parameters Not used
     * @param object $pObj Not used
     * @return string
     */
    public function renderField(&$parameters, &$pObj)
    {
        unset($pObj);
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
            return LocalizationUtility::translate('user.no_selection', 'Flux');
        }
        unset($parameters);
        return null;
    }
}
