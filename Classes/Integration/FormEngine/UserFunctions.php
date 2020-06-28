<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
     * User function for TCA fields to hide a Flux-enabled "flex" type field if
     * there are no fields in the DS.
     *
     * @param array $parameters
     * @param $pObj
     * @return bool
     */
    public function fluxFormFieldDisplayCondition(array $parameters, &$pObj)
    {
        list ($table, $field) = $parameters['conditionParameters'];
        $provider = GeneralUtility::makeInstance(ObjectManager::class)
            ->get(ProviderResolver::class)
            ->resolvePrimaryConfigurationProvider($table, $field, $parameters['record']);

        if (!$provider) {
            return true;
        }
        $form = $provider->getForm($parameters['record']);
        if ($form) {
            return count($form->getFields()) > 0;
        }
        return false;
    }

    /**
     * @param array $parameters
     * @return mixed
     */
    public function renderHtmlOutputField(array &$parameters)
    {
        return trim(($parameters['fieldConf']['config']['parameters']['closure'] ?? $parameters['parameters']['closure'])($parameters));
    }

    /**
     * Renders the special "column position" field that's used inside section objects
     * and which stores a unique integer value. The method is designed to scan for
     * all colPos currently holding content elements in the the database. The
     * client-side JavaScript merges these values with all colPos currently defined
     * in the form in the browser window to determine the next colPos value that's
     * not already occupied by records.
     * The next free value cannot be computed here as we do not have access to the
     * data of all potentially unsaved section objects.
     *
     * @param array $parameters
     * @param object $pObj Not used
     * @return mixed
     */
    public function renderColumnPositionField(array &$parameters, &$pObj = null)
    {
        $colPos = $parameters['itemFormElValue'];
        $inputValue = (string) $colPos;

        $id = StringUtility::getUniqueId('formengine-flux-colPos-');

        if ($inputValue !== '') {
            // The field already has a value, just use that for the hidden input element
            return sprintf(
                '<input type="hidden" name="%s" id="%s" class="flux-flex-colPos-input" value="%s" />Column position: <strong class="flux-flex-colPos-text">%d</strong>',
                $parameters['itemFormElName'],
                $id,
                $inputValue,
                $inputValue
            );
        } else {
            // The field does not yet have a value, which means this is used for a new panel
            // and we have to fill the fields that will be used by the JavaScript module to
            // determine the value
            $rowUid = $parameters['row']['uid'];
            // Unsaved records may begin with "NEW", make sure we don't have one of those
            // as we cannot look up anything in the database in that case
            if (!isset($rowUid) || !is_int($rowUid)) {
                $rowUid = 0;
            }

            $minimumColumnPosition = 0;
            $maximumColumnPosition = ColumnNumberUtility::MULTIPLIER - 1;
            $takenColumnPositions = $this->determineTakenColumnPositionsWithinParent('tt_content', $rowUid);

            return sprintf(
                '<input type="hidden" name="%s" id="%s" class="flux-flex-colPos-input" data-min-value="%d" data-max-value="%d" data-taken-values="%s" />Column position: <strong class="flux-flex-colPos-text"></strong>',
                $parameters['itemFormElName'],
                $id,
                $minimumColumnPosition,
                $maximumColumnPosition,
                implode(',', $takenColumnPositions)
            );
        }
    }

    protected function determineTakenColumnPositionsWithinParent(string $table, int $parentUid) : array
    {
        if ($parentUid === 0) {
            return [];
        }
        list ($minimumColPosValue, $maximumColPosValue) = ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent($parentUid);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('colPos')->from($table)->andWhere(
            $queryBuilder->expr()->gte('colPos', $minimumColPosValue),
            $queryBuilder->expr()->lt('colPos', $maximumColPosValue)
        );
        $rows = $query->execute()->fetchAll();
        return empty($rows) ? [] : array_map(function ($colPos) { return ColumnNumberUtility::calculateLocalColumnNumber($colPos); }, array_unique(array_column($rows, 'colPos')));
    }
}
