<?php
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
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\Utility\ColumnNumberUtility;
use FluidTYPO3\Flux\ViewHelpers\FormViewHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

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
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var FluxService
     */
    protected static $configurationService;

    /**
     * @var ConfigurationManagerInterface
     */
    protected static $configurationManager;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected static $recordService;

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
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
    }

    /**
     * Default implementation for use in compiled templates
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        static::$configurationService = static::$configurationService ?? $objectManager->get(FluxService::class);
        static::$configurationManager = static::$configurationManager ?? $objectManager->get(ConfigurationManagerInterface::class);

        $contentObjectRenderer = static::getContentObjectRenderer();

        $loadRegister = false;
        if (empty($arguments['loadRegister']) === false) {
            $contentObjectRenderer->cObjGetSingle('LOAD_REGISTER', $arguments['loadRegister']);
            $loadRegister = true;
        }
        $templateVariableContainer = $renderingContext->getVariableProvider();
        $record = (array) $renderingContext->getViewHelperVariableContainer()->get(FormViewHelper::class, 'record');

        if ($GLOBALS['BE_USER']->workspace) {
            $placeholder = BackendUtility::getMovePlaceholder('tt_content', $record['uid']);
            if ($placeholder) {
                // Use the move placeholder if one exists, ensuring that "pid" and "tx_flux_parent" values are taken
                // from the workspace-only placeholder.
                $record = $placeholder;
            }
        }

        $grid = $renderingContext->getViewHelperVariableContainer()->get(FormViewHelper::class, 'provider')->getGrid($record);
        $rows = static::getContentRecords($arguments, $record, $grid);

        $elements = false === (boolean) $arguments['render'] ? $rows : static::getRenderedRecords($rows);
        if (true === empty($arguments['as'])) {
            $content = $elements;
        } else {
            $as = $arguments['as'];
            if (true === $templateVariableContainer->exists($as)) {
                $backup = $templateVariableContainer->get($as);
                $templateVariableContainer->remove($as);
            }
            $templateVariableContainer->add($as, $elements);
            $content = $renderChildrenClosure();
            $templateVariableContainer->remove($as);
            if (true === isset($backup)) {
                $templateVariableContainer->add($as, $backup);
            }
        }
        if ($loadRegister) {
            $contentObjectRenderer->cObjGetSingle('RESTORE_REGISTER', '');
        }
        return $content;
    }

    /**
     * @param array $arguments
     * @param array $parent
     * @param Grid $grid
     * @return array
     */
    protected static function getContentRecords(array $arguments, array $parent, Grid $grid)
    {
        $columnPosition = $arguments['area'];
        if (!is_numeric($columnPosition)) {
            $column = $grid->get($columnPosition, true, Column::class);
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

        $conditions = sprintf(
            'colPos = %d',
            ColumnNumberUtility::calculateColumnNumberForParentAndColumn(
                $parent['l18n_parent'] ?: $parent['uid'],
                $columnPosition
            )
        );

        $rows = static::getContentObjectRenderer()->getRecords(
            'tt_content',
            [
                'max' => $arguments['limit'],
                'begin' => $arguments['offset'],
                'orderBy' => $arguments['order'] . ' ' . $arguments['sortDirection'],
                'where' => $conditions,
                'pidInList' => $parent['pid'],
                'includeRecordsWithoutDefaultTranslation' => !$arguments['hideUntranslated']
            ]
        );

        return HookHandler::trigger(
            HookHandler::NESTED_CONTENT_FETCHED,
            [
                'records' => $rows
            ]
        )['records'];
    }

    /**
     * @return WorkspacesAwareRecordService
     */
    protected static function getRecordService()
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(WorkspacesAwareRecordService::class);
    }

    /**
     * @return ContentObjectRenderer
     */
    protected static function getContentObjectRenderer()
    {
        return $GLOBALS['TSFE']->cObj;
    }

    /**
     * This function renders an array of tt_content record into an array of rendered content
     * it returns a list of elements rendered by typoscript RECORDS function
     *
     * @param array $rows database rows of records (each item is a tt_content table record)
     * @return array
     */
    protected static function getRenderedRecords($rows)
    {
        $elements = [];
        foreach ($rows as $row) {
            $conf = [
                'tables' => 'tt_content',
                'source' => $row['uid'],
                'dontCheckPid' => 1,
            ];
            array_push($elements, static::getContentObjectRenderer()->cObjGetSingle('RECORDS', $conf));
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
