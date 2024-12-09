<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Hooks\HookHandler;
use FluidTYPO3\Flux\Provider\AbstractProvider;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Gets all child content of a record based on area.
 *
 * The elements are already rendered, they just need to be output.
 *
 * ### Example: Render all child elements with a border
 *
 * `fluidcontent` element with one column of child elements.
 * Each element gets a red border:
 *
 *     <f:section name="Configuration">
 *      <flux:grid>
 *       <flux:grid.row>
 *        <flux:grid.column name="teaser" colPos="0"/>
 *       </flux:grid.row>
 *      </flux:grid>
 *     </f:section>
 *
 *     <f:section name="Main">
 *      <f:for each="{flux:content.get(area:'teaser')}" as="element">
 *       <div style="border: 1px solid red">
 *        <f:format.raw>{element}</f:format.raw>
 *       </div>
 *      </f:for>
 *     </f:section>
 */
class GetViewHelper extends AbstractViewHelper
{
    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    protected static ?ConfigurationManagerInterface $configurationManager = null;
    protected static ?WorkspacesAwareRecordService $recordService = null;

    public function initializeArguments(): void
    {
        $this->registerArgument('area', 'string', 'Name or "colPos" value of the content area to render', true);
        $this->registerArgument('limit', 'integer', 'Optional limit to the number of content elements to render');
        $this->registerArgument('offset', 'integer', 'Optional offset to the limit', false, 0);
        $this->registerArgument(
            'order',
            'string',
            'Optional sort order of content elements - RAND() supported',
            false,
            'sorting'
        );
        $this->registerArgument('sortDirection', 'string', 'Optional sort direction of content elements', false, 'ASC');
        $this->registerArgument(
            'as',
            'string',
            'Variable name to register, then render child content and insert all results as an array of records'
        );
        $this->registerArgument('loadRegister', 'array', 'List of LOAD_REGISTER variable');
        $this->registerArgument('render', 'boolean', 'Optional returning variable as original table rows', false, true);
        $this->registerArgument('hideUntranslated', 'boolean', 'Exclude untranslated records', false, false);
    }

    /**
     * @return array|string|null
     */
    public function render()
    {
        return static::renderStatic($this->arguments, $this->buildRenderChildrenClosure(), $this->renderingContext);
    }

    /**
     * @return string|array|null
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $contentObjectRenderer = static::getContentObjectRenderer();

        $registerVariables = (array) $arguments['loadRegister'];
        $loadRegister = false;
        if (!empty($registerVariables)) {
            $contentObjectRenderer->cObjGetSingle('LOAD_REGISTER', $registerVariables);
            $loadRegister = true;
        }
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $record = $renderingContext->getViewHelperVariableContainer()->get(FormViewHelper::class, 'record');
        if (!is_array($record)) {
            return null;
        }

        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $workspaceId = $context->getPropertyFromAspect('workspace', 'id');

        if (is_numeric($workspaceId) && $workspaceId > 0) {
            $placeholder = BackendUtility::getWorkspaceVersionOfRecord(
                (integer) $workspaceId,
                'tt_content',
                $record['uid'] ?? 0
            );
            if ($placeholder) {
                // Use the move placeholder if one exists, ensuring that "pid" and "tx_flux_parent" values are taken
                // from the workspace-only placeholder.
                /** @var array $record */
                $record = $placeholder;
            }
        }

        /** @var AbstractProvider $provider */
        $provider = $renderingContext->getViewHelperVariableContainer()->get(FormViewHelper::class, 'provider');
        $grid = $provider->getGrid($record);
        $rows = static::getContentRecords($arguments, $record, $grid);

        $elements = false === (boolean) $arguments['render'] ? $rows : static::getRenderedRecords($rows);
        if (empty($arguments['as'])) {
            $content = $elements;
        } else {
            /** @var string $as */
            $as = $arguments['as'];
            if ($templateVariableContainer->exists($as)) {
                $backup = $templateVariableContainer->get($as);
                $templateVariableContainer->remove($as);
            }
            $templateVariableContainer->add($as, $elements);
            $content = $renderChildrenClosure();
            $templateVariableContainer->remove($as);
            if (isset($backup)) {
                $templateVariableContainer->add($as, $backup);
            }
        }
        if ($loadRegister) {
            $contentObjectRenderer->cObjGetSingle('RESTORE_REGISTER', []);
        }
        return $content;
    }

    protected static function getContentRecords(array $arguments, array $parent, Grid $grid): array
    {
        $columnPosition = $arguments['area'];
        if (!ctype_digit((string) $columnPosition)) {
            $column = $grid->get((string) $columnPosition, true, Column::class);
            if ($column instanceof Column) {
                $columnPosition = $column->getColumnPosition();
            } else {
                throw new Exception(
                    sprintf(
                        'Argument "column" or "area" for "flux:content.(get|render)" was a string column name "%s", ' .
                        'but this column was not defined',
                        $columnPosition
                    )
                );
            }
        }

        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable('tt_content');

        $conditions = $queryBuilder->expr()->eq(
            'colPos',
            ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                ($parent['l18n_parent'] ?? null) ?: $parent['uid'],
                (int) $columnPosition
            )
        );

        $rows = static::getContentObjectRenderer()->getRecords(
            'tt_content',
            [
                'max' => $arguments['limit'],
                'begin' => $arguments['offset'],
                'orderBy' => $arguments['order'] . ' ' . $arguments['sortDirection'],
                'where' => $conditions,
                'pidInList' => $parent['pid'] ?? null,
                'includeRecordsWithoutDefaultTranslation' => !($arguments['hideUntranslated'] ?? false)
            ]
        );

        return HookHandler::trigger(
            HookHandler::NESTED_CONTENT_FETCHED,
            [
                'records' => $rows
            ]
        )['records'];
    }

    protected static function getContentObjectRenderer(): ContentObjectRenderer
    {
        return $GLOBALS['TSFE']->cObj;
    }

    /**
     * This function renders an array of tt_content record into an array of rendered content
     * it returns a list of elements rendered by typoscript RECORDS function
     */
    protected static function getRenderedRecords(array $rows): array
    {
        $elements = [];
        foreach ($rows as $row) {
            $conf = [
                'tables' => 'tt_content',
                'source' => $row['uid'],
                'dontCheckPid' => 1,
            ];
            $elements[] = static::getContentObjectRenderer()->cObjGetSingle('RECORDS', $conf);
        }
        return HookHandler::trigger(
            HookHandler::NESTED_CONTENT_RENDERED,
            [
                'rows' => $rows,
                'rendered' => $elements
            ]
        )['rendered'];
    }
}
