<?php
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use TYPO3\CMS\Backend\View\BackendLayout\BackendLayout;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;

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

    /**
     * @param int $parentRecordUid
     * @return array
     */
    public function buildBackendLayoutArray(int $parentRecordUid)
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
                    'colPos' => ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                        $parentRecordUid,
                        $column->getColumnPosition()
                    )
                ];
                if ($column->getColspan()) {
                    $columns[$key]['colspan'] = $column->getColspan();
                }
                if ($column->getRowspan()) {
                    $columns[$key]['rowspan'] = $column->getRowspan();
                }
                $colCount += $column->getColspan() ? $column->getColspan() : 1;
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

    public function buildExtendedBackendLayoutArray(int $parentRecordUid)
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
                $key = ($index + 1) . '.';
                $columns[$key] = [
                    'name' => $column['label'] ?? $column['name'],
                    'colPos' => (string)$column['colPos']
                ];
                if ($column['colspan']) {
                    $columns[$key]['colspan'] = $column['colspan'];
                }
                if ($column['rowspan']) {
                    $columns[$key]['rowspan'] = $column['rowspan'];
                }
                $colPosList[$columns[$key]['colPos']] = $columns[$key]['colPos'];
                array_push($items, [$columns[$key]['name'], $columns[$key]['colPos'], null]);
                $colCount += $column['colspan'] ? $column['colspan'] : 1;
                ++ $index;
            }
            ++ $rowIndex;
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
    public function buildBackendLayout(int $parentRecordUid)
    {
        $configuration = $this->buildBackendLayoutArray($parentRecordUid);
        $configuration = $this->ensureDottedKeys($configuration);
        $typoScriptParser = new ExtendedTemplateService();
        $typoScriptParser->flattenSetup($configuration, 'backend_layout.', false);
        $typoScriptString = '';
        foreach ($typoScriptParser->flatSetup as $name => $value) {
            $typoScriptString .= $name . ' = ' . $value . LF;
        }
        return new BackendLayout($this->getRoot()->getName(), $this->getRoot()->getExtensionName(), $typoScriptString);
    }

    /**
     * @param array $configuration
     * @return array
     */
    protected function ensureDottedKeys(array $configuration)
    {
        $converted = [];
        foreach ($configuration as $key => $value) {
            if (true === is_array($value)) {
                $key = rtrim($key, '.') . '.';
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
