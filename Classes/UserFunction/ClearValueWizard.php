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
 * Renders a checkbox which, when checked, clears a flexform field value.
 */
class ClearValueWizard
{

    /**
     * @param array $parameters
     * @param object $pObj Not used
     * @return string
     */
    public function renderField(&$parameters, &$pObj)
    {
        unset($pObj);
        $nameSegments = explode('][', $parameters['itemName']);
        $nameSegments[6] .= '_clear';
        $fieldName = implode('][', $nameSegments);
        $html = '<label style="opacity: 0.65; padding-left: 2em"><input type="checkbox" name="' . $fieldName .
            '_clear"  value="1" /> ' . LocalizationUtility::translate('flux.clearValue', 'Flux') . '</label>';
        return $html;
    }
}
