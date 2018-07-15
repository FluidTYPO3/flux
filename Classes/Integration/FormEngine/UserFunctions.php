<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\Interfaces\GridProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @param array $parameters
     * @param object $pObj Not used
     * @return mixed
     */
    public function renderHtmlOutputField(array &$parameters, &$pObj)
    {
        unset($pObj);
        return trim($parameters['parameters']['closure']($parameters));
    }

    /**
     * Renders the special "column position" field that's used inside section objects
     * and which stores a unique integer value. The method is designed to scan for
     * all currently defined colPos values as well as query the database to determine
     * the next colPos value that's not already occupied by records, and to generate
     * sequential column position values while creating section objects inside a not
     * yet persisted parent element; or when creating multiple new section objects in
     * an already persisted parent.
     *
     * @param array $parameters
     * @param object $pObj Not used
     * @return mixed
     */
    public function renderColumnPositionField(array &$parameters, &$pObj)
    {
        $path = explode('[', trim($parameters['itemFormElName'], ']['));
        $path = array_map(function($item) { return trim($item, ']'); }, $path);
        $valuePath = array_slice($path, -5);
        $path = array_slice($path, 3, -5);
        $objectsPointer = $parameters['row'];
        foreach ($path as $pathSegment) {
            $objectsPointer = &$objectsPointer[$pathSegment];
        }
        $valuePointer = $objectsPointer;
        foreach ($valuePath as $pathSegment) {
            $valuePointer = &$valuePointer[$pathSegment];
        }
        $numberOfObjects = count($objectsPointer) - 1;
        if ((string) $valuePointer === '') {
            $valuePointer = $this->determineFirstFreeColumnPositionWithinParent($parameters['table'], $parameters['row']['uid'] ?? 0) ?: $numberOfObjects;
        }
        return sprintf(
            '<input type="hidden" name="%s" id="%s" value="%d" />Column position: <strong>%d</strong>',
            $parameters['itemFormElName'],
            $parameters['itemFormElID'],
            $valuePointer,
            $valuePointer
        );
    }

    protected function determineFirstFreeColumnPositionWithinParent(string $table, int $parentUid): int
    {
        if ($parentUid === 0) {
            return 0;
        }
        list ($minimumColPosValue, $maximumColPosValue) = ColumnNumberUtility::calculateMinimumAndMaximumColumnNumberWithinParent($parentUid);
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $query = $queryBuilder->select('colPos')->from($table)->andWhere(
            $queryBuilder->expr()->gte('colPos', $minimumColPosValue),
            $queryBuilder->expr()->lt('colPos', $maximumColPosValue)
        );
        $rows = $query->execute()->fetchAll();
        $values = empty($rows) ? [] : array_column($rows, 'colPos');
        return empty($values) ? 0 : ColumnNumberUtility::calculateLocalColumnNumber(max($values) + 1);
    }
}
