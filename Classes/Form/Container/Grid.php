<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Grid
 */
class Grid extends AbstractFormContainer implements ContainerInterface
{

    /**
     * @return array
     */
    public function build()
    {
        $structure = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'rows' => $this->buildChildren($this->children)
        ];
        return $structure;
    }

    public function buildColumnPositionValues(array $record): array
    {
        $columnPositionValues = [];
        $parentRecordUid = $record['l18n_parent'] ?: $record['uid'];
        foreach ($this->getRows() as $row) {
            foreach ($row->getColumns() as $column) {
                $columnPositionValues[] = ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                    $parentRecordUid,
                    $column->getColumnPosition()
                );
            }
        }
        return $columnPositionValues;
    }

    /**
     * @param int $parentRecordUid
     * @return array
     */
    public function buildBackendLayoutArray(int $parentRecordUid): array
    {
        $config = [
            'colCount' => 0,
            'rowCount' => 0,
            'rows.' => []
        ];
        $rowIndex = 0;
        foreach ($this->getRows() as $row) {
            $index = 0;
            $colCount = 0;
            $rowKey = ($rowIndex + 1) . '.';
            $columns = [];
            foreach ($row->getColumns() as $column) {
                $key = ($index + 1) . '.';
                $columns[$key] = [
                    'name' => $column->getLabel(),
                    'icon' => $column->getVariable(Form::OPTION_ICON),
                    'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $parentRecordUid,
                        $column->getColumnPosition()
                    )
                ];
                $columns[$key]['colspan'] = $column->getColspan() ?: 1;
                $columns[$key]['rowspan'] = $column->getRowspan() ?: 1;
                $colCount += ($column->getColspan() ?: 1);
                ++ $index;
            }
            $config['colCount'] = max($config['colCount'], $colCount);
            $config['rowCount']++;
            $config['rows.'][$rowKey] = [
                'columns.' => $columns
            ];
            ++ $rowIndex;
        }
        return $config;
    }

    public function buildExtendedBackendLayoutArray(int $parentRecordUid): array
    {
        $config = $this->buildBackendLayoutArray($parentRecordUid);

        $colPosList = [];
        $items = [];
        $rowIndex = 0;
        foreach ($config['rows.'] as $row) {
            $index = 0;
            $colCount = 0;
            $columns = [];
            foreach ($row['columns.'] as $column) {
                $colPos = (int)$column['colPos'];
                $key = ($index + 1) . '.';
                $columns[$key] = $column;
                $colPosList[$colPos] = $colPos;
                $items[] = [
                    $columns[$key]['name'],
                    $colPos,
                    $column['icon']
                ];
                $colCount += $column['colspan'] ? $column['colspan'] : 1;
                $backendLayout['usedColumns'][$colPos] = $column['name'];
                ++ $index;
            }
            ++ $rowIndex;
        }

        if ($parentRecordUid === 0) {
            // We are creating a grid for the page level backend layout. Add colPos item values from TCA if they were
            // not defined as grid columns and are above ColumnNumberCalculator::MULTIPLIER.
            foreach ($GLOBALS['TCA']['tt_content']['columns']['colPos']['config']['items'] as $columnSelectionOption) {
                if ($columnSelectionOption[1] > ColumnNumberUtility::MULTIPLIER && !in_array($columnSelectionOption, $items, true)) {
                    // This is in all likelihood a virtual column; include it.
                    $items[] = $columnSelectionOption;
                }
            }
        }

        $backendLayout['__config'] = ['backend_layout.' => $config];
        $backendLayout['__colPosList'] = $colPosList;
        $backendLayout['__items'] = $items;

        return $backendLayout;
    }

    /**
     * @param int $parentRecordUid
     * @return BackendLayout
     */
    public function buildBackendLayout(int $parentRecordUid): BackendLayout
    {
        $configuration = $this->buildBackendLayoutArray($parentRecordUid);
        $configuration = $this->ensureDottedKeys($configuration);

        $typoScriptString = '';
        $root = $this->getRoot();
        $label = $root->getLabel();
        foreach ($this->flattenSetup($configuration, 'backend_layout.') as $name => $value) {
            $typoScriptString .= $name . ' = ' . $value . LF;
        }
        return new BackendLayout(
            $this->getRoot()->getName(),
            LocalizationUtility::translate($label)
                ? $label
                : 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:flux.grid.grids.grid',
            $typoScriptString
        );
    }

    /**
     * This flattens a hierarchical TypoScript array to $this->flatSetup
     *
     * @param iterable $setupArray TypoScript array
     * @param string $prefix Prefix to the object path. Used for recursive calls to this function.
     * @see generateConfig()
     */
    protected function flattenSetup(iterable $setupArray, $prefix): array
    {
        $setup = [];
        foreach ($setupArray as $key => $val) {
            if (is_array($val)) {
                $setup = array_merge(
                    $setup,
                    $this->flattenSetup($val, $prefix . $key)
                );
            } else {
                $setup[$prefix . $key] = $val;
            }
        }
        return $setup;
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function ensureDottedKeys(array $configuration): array
    {
        $converted = [];
        foreach ($configuration as $key => $value) {
            if (true === is_array($value)) {
                $key = rtrim((string) $key, '.') . '.';
                $value = $this->ensureDottedKeys($value);
            }
            $converted[$key] = $value;
        }
        return $converted;
    }

    /**
     * @return Row[]
     */
    public function getRows()
    {
        return (array) iterator_to_array($this->children);
    }
}
