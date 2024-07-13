<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * TCA field wizard to protect the value from changing when inherited value changes.
 */
class ProtectValueWizard extends AbstractNode
{
    public function render(): array
    {
        $result = $this->initializeResultArray();

        $fieldName = 'data' . $this->data['elementBaseName'];
        $nameSegments = explode('][', $fieldName);
        $wizardFieldName = $nameSegments[count($nameSegments) - 2] .= '_protect';
        $wizardFieldValue = $this->data['flexFormRowData'][$wizardFieldName]['vDEF'] ?? false;
        $fieldName = implode('][', $nameSegments);
        $checked = (bool) $wizardFieldValue ? ' checked="checked"' : '';

        $result['html'] = '<input type="hidden" name="' . $fieldName . '" value="0" />';
        $result['html'] .= '<label style="opacity: 0.65; margin-top: 1em; margin-right: 1em;" title="'
            . $this->translate('flux.protectValue.help')
            . '">'
            . '<input type="checkbox" class="form-check-input" name="'
            . $fieldName
            . '" value="1"'
            . $checked
            . '/> '
            . $this->translate('flux.protectValue')
            . '</label>';

        return $result;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function translate(string $label): ?string
    {
        return LocalizationUtility::translate($label, 'Flux');
    }
}
