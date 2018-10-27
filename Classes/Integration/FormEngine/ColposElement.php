<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\StringUtility;


/**
 * Generation of elements of the type "user"
 */
class ColposElement extends AbstractFormElement
{
    /**
     * User defined field type
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render()
    {
        $resultArray = $this->initializeResultArray();

        $parameters = $this->data['parameterArray'];
        $colPos = $parameters['itemFormElValue'];
        $inputValue = (string) $colPos;

        $id = StringUtility::getUniqueId('formengine-flux-colPos-');
        $resultArray['html'] = sprintf(
            '<input type="hidden" name="%s" id="%s" class="flux-flex-colPos-input" value="%s" />Column position: <strong class="flux-flex-colPos-text">%d</strong>',
            $parameters['itemFormElName'],
            $id,
            $inputValue,
            $inputValue
        );

        if ($inputValue === '') {
            $rowUid = $this->data['databaseRow']['uid'];
            if (!isset($rowUid) || !is_int($rowUid)) {
                $rowUid = 0;
            }
            $usedColumnPositions = $this->determineUsedColumnPositionsWithinParent('tt_content', $rowUid);
            $usedColumnPositionsJS = '[' . implode(',', $usedColumnPositions) . ']';
            // FIXME better constant?
            $maximumColPos = 99;

            $resultArray['additionalJavaScriptPost'][] = sprintf("(function() {
                var input = $('#%s');
                var dbUsedColPos = %s;
                var container = input.closest('.t3-flex-container');
                var jsUsedColPos = container.find('.t3js-flex-section:not(.t3js-flex-section-deleted) .flux-flex-colPos-input').map(function() { return parseInt(this.value); }).get();
                for (var colPos = 0; colPos <= %d; colPos++) {
                    if (dbUsedColPos.indexOf(colPos) === -1 && jsUsedColPos.indexOf(colPos) === -1) {
                        break;
                    }
                }
                input.val(colPos);
                input.closest('.formengine-field-item').find('.flux-flex-colPos-text').text(colPos);
            })();",
            $id,
            $usedColumnPositionsJS,
            $maximumColPos
        );
        }
        return $resultArray;
    }

    protected function determineUsedColumnPositionsWithinParent(string $table, int $parentUid) : array
    {
        if ($parentUid === 0) {
            return [];
        }
        list($minimumColPosValue, $maximumColPosValue) = ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent($parentUid);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('colPos')->from($table)->andWhere(
            $queryBuilder->expr()->gte('colPos', $minimumColPosValue),
            $queryBuilder->expr()->lt('colPos', $maximumColPosValue)
        );
        $rows = $query->execute()->fetchAll();
        return empty($rows) ? [] : array_map(function ($colPos) { return ColumnNumberUtility::calculateLocalColumnNumber($colPos); }, array_unique(array_column($rows, 'colPos')));
    }
}
