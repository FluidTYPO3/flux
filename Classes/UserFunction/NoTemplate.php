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
class NoTemplate
{

    /**
     * @param array $parameters Not used
     * @param object $pObj Not used
     * @return string|NULL
     */
    public function renderField(&$parameters, &$pObj)
    {
        unset($pObj);
        if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] > 0) {
            $message = LocalizationUtility::translate('user.no_template', 'Flux');
            $parameterKeys = var_export(array_keys($parameters), true);
            return $message . '<pre>' . $parameterKeys . '</pre>';
        }
        unset($parameters);
        return null;
    }
}
