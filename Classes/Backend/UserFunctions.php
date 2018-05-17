<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class UserFunctions
{
    /**
     * @param array $parameters
     * @param object $pObj Not used
     * @return string
     */
    public function renderClearValueWizardField(&$parameters, &$pObj)
    {
        unset($pObj);
        $nameSegments = explode('][', $parameters['itemName']);
        $nameSegments[6] .= '_clear';
        $fieldName = implode('][', $nameSegments);
        $html = '<label style="opacity: 0.65; padding-left: 2em"><input type="checkbox" name="' . $fieldName .
            '_clear"  value="1" /> ' . LocalizationUtility::translate('flux.clearValue', 'Flux') . '</label>';
        return $html;
    }

    /**
     * @param array $parameters
     * @param object $pObj Not used
     * @return mixed
     */
    public function renderHtmlOutputField(array &$parameters, &$pObj)
    {
        unset($pObj);
        return trim($parameters['parameters']['closure']($parameters));
    }
}
