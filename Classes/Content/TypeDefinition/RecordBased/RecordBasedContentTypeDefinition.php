<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Content\TypeDefinition\RecordBased;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Content\RuntimeDefinedContentProvider;
use FluidTYPO3\Flux\Content\TypeDefinition\FluidRenderingContentTypeDefinitionInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\SerializeSafeInterface;
use FluidTYPO3\Flux\Content\TypeDefinition\SerializeSafeTrait;
use FluidTYPO3\Flux\Enum\FormOption;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Row;
use FluidTYPO3\Flux\Form\Container\Section;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Record-based Content Type Definition
 *
 * Implementation of ContentTypeDefinition which is based on
 * database records containing properties edited as TCA fields.
 */
class RecordBasedContentTypeDefinition implements FluidRenderingContentTypeDefinitionInterface, SerializeSafeInterface
{
    use SerializeSafeTrait;

    protected array $record = [];
    protected string $contentTypeName = '';
    protected ?Grid $grid;
    protected static array $types = [];

    public function __construct(array $record)
    {
        $this->record = $record;
        $this->contentTypeName = $record['content_type'] ?? 'undefined';
    }

    /**
     * @return RecordBasedContentTypeDefinition[]
     */
    public static function fetchContentTypes(): array
    {
        if (empty(static::$types)) {
            /** @var RecordBasedContentTypeDefinitionRepository $definitionRepository */
            $definitionRepository = GeneralUtility::makeInstance(RecordBasedContentTypeDefinitionRepository::class);
            static::$types = $definitionRepository->fetchContentTypeDefinitions();
        }
        return static::$types;
    }

    public function getProviderClassName(): string
    {
        return RuntimeDefinedContentProvider::class;
    }

    public function getContentTypeName(): string
    {
        return $this->contentTypeName;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getContentConfiguration(): array
    {
        $configuration = $this->record['content_configuration'] ?? [];
        if (is_array($configuration)) {
            return $configuration;
        }
        return (array) GeneralUtility::xml2array($configuration);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getGridConfiguration(): array
    {
        $configuration = $this->record['grid'] ?? [];
        if (is_array($configuration)) {
            return $configuration;
        }
        return (array) GeneralUtility::xml2array($configuration);
    }

    public function getSheetNamesAndLabels(): \Generator
    {
        foreach ($this->getContentConfiguration() as $item) {
            foreach ($item['sheets']['lDEF']['sheets']['el'] ?? [] as $sheetObjectData) {
                yield $sheetObjectData['sheet']['el']['name']['vDEF']
                    => 'Sheet: ' . $sheetObjectData['sheet']['el']['label']['vDEF'];
            }
        }
    }

    public function getForm(array $record = []): Form
    {
        $instance = Form::create();
        $instance->remove('options');
        $instance->setOption(FormOption::ICON, $this->getIconReference());
        $instance->setOption(FormOption::GROUP, 'fluxContent');
        $instance->setOption(FormOption::SORTING, $this->record['sorting']);
        $instance->setLabel($this->record['title']);
        $instance->setDescription($this->record['description']);
        foreach ($this->getContentConfiguration() as $item) {
            foreach ($item['sheets']['lDEF']['sheets'] ?? [] as $sheetObjects) {
                foreach ($sheetObjects as $sheetData) {
                    $sheetValues = $sheetData['sheet']['el'];
                    $sheet = $instance->createContainer(
                        Form\Container\Sheet::class,
                        $sheetValues['name']['vDEF'],
                        $sheetValues['label']['vDEF']
                    );

                    foreach ($item[$sheetValues['name']['vDEF']]['lDEF']['fields']['el'] ?? [] as $fieldObject) {
                        /** @var class-string $fieldType */
                        $fieldType = ucfirst(key($fieldObject));

                        $fieldSettings = reset($fieldObject)['el'];
                        foreach ($fieldSettings as $key => $value) {
                            $fieldSettings[$key] = $value['vDEF'];
                        }

                        /** @var Form\FieldInterface $field */
                        $field = $sheet->createField($fieldType, $fieldSettings['name']);
                        $field->modify($fieldSettings);
                    }
                }
            }
        }

        return $instance;
    }

    public function getGrid(array $record = []): ?Grid
    {
        if (!empty($this->grid)) {
            return $this->grid;
        }
        foreach ($this->getGridConfiguration() as $item) {
            $gridMode = $item['grid']['lDEF']['gridMode']['vDEF'] ?? Section::GRID_MODE_ROWS;
            $autoColumns = (int)($item['grid']['lDEF']['autoColumns']['vDEF'] ?? 0);

            /** @var Grid $grid */
            $grid = Grid::create();

            $currentNumberOfColumns = 0;

            if ($gridMode === Section::GRID_MODE_ROWS) {
                foreach ($item['grid']['lDEF']['columns'] ?? [] as $index => $columnObjects) {
                    foreach ($columnObjects as $columnObject) {
                        $name = $columnObject['column']['el']['name']['vDEF'];
                        $label = $columnObject['column']['el']['label']['vDEF'];
                        $row = $grid->createContainer(Row::class, 'row' . $index);
                        $column = $row->createContainer(Column::class, $name, $label);
                        $column->setColumnPosition((int)$columnObject['column']['el']['colPos']['vDEF']);

                        ++$currentNumberOfColumns;
                    }
                }
            } else {
                foreach ($item['grid']['lDEF']['columns'] ?? [] as $index => $columnObjects) {
                    $row = $grid->createContainer(Row::class, 'row' . $index);
                    foreach ($columnObjects as $columnObject) {
                        $name = $columnObject['column']['el']['name']['vDEF'];
                        $label = $columnObject['column']['el']['label']['vDEF'];
                        $column = $row->createContainer(Column::class, $name, $label);
                        $column->setColumnPosition((int)$columnObject['column']['el']['colPos']['vDEF']);

                        ++$currentNumberOfColumns;
                    }
                }
            }

            if ($autoColumns) {
                $this->createAutomaticGridColumns($grid, $currentNumberOfColumns, $autoColumns, $gridMode);
            }

            return $grid;
        }
        return null;
    }

    public function getIconReference(): string
    {
        return $this->record['icon'];
    }

    public function getExtensionIdentity(): string
    {
        return $this->record['extension_identity'];
    }

    public function isUsingTemplateFile(): bool
    {
        return !empty($this->record['template_file']);
    }

    public function isUsingGeneratedTemplateSource(): bool
    {
        return empty($this->record['template_file']) && empty($this->record['template_source']);
    }

    public function getTemplatePathAndFilename(): string
    {
        if (!$this->isUsingTemplateFile()) {
            return ExtensionManagementUtility::extPath('flux', 'Resources/Private/Templates/Content/Proxy.html');
        }
        return $this->record['template_file'];
    }

    public function getTemplateSource(): string
    {
        if ($this->isUsingGeneratedTemplateSource()) {
            // The content type has neither template source nor file.
            // Generate an extremely basic source for the configured grid mode.

            $columnTemplateChunk = '<flux:content.render area="%d" />' . PHP_EOL;

            $grid = $this->getGrid();
            if (!($grid instanceof Grid)) {
                return '';
            }
            $template = '';
            if (!empty($this->record['template_source'])) {
                $template .= $this->record['template_source'] . PHP_EOL;
            }
            $template .= '<div class="flux-grid">' . PHP_EOL;
            foreach ($grid->getRows() as $row) {
                $template .= '<div class="flux-grid-row">' . PHP_EOL;
                foreach ($row->getColumns() as $column) {
                    $template .= sprintf($columnTemplateChunk, $column->getColumnPosition());
                }
                $template .= '</div>' . PHP_EOL;
            }
            $template .= '</div>' . PHP_EOL;
            return $template;
        }
        return $this->record['template_source'];
    }

    protected function createAutomaticGridColumns(
        Grid $grid,
        int $currentNumberOfColumns,
        int $totalNumberOfColumns,
        string $mode
    ): void {
        if ($mode === Section::GRID_MODE_ROWS) {
            for ($i = $currentNumberOfColumns; $i < $totalNumberOfColumns; ++$i) {
                $grid->createContainer(Row::class, 'row' . $i)
                    ->createContainer(Column::class, 'content' . $i)
                    ->setColumnPosition($i);
            }
        } else {
            $row = $grid->createContainer(Row::class, 'row');
            for ($i = $currentNumberOfColumns; $i < $totalNumberOfColumns; ++$i) {
                $row->createContainer(Column::class, 'content' . $i)->setColumnPosition($i);
            }
        }
    }
}
