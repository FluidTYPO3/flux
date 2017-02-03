<?php
namespace FluidTYPO3\Flux\ViewHelpers\Content;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * ### Content: Get ViewHelper
 *
 * Gets all child content of a record based on area.
 */
class GetViewHelper extends AbstractViewHelper implements CompilableInterface
{
    use CompileWithRenderStatic;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @var \FluidTYPO3\Flux\Service\FluxService
     * @inject
     */
    protected static $configurationService;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @inject
     */
    protected static $configurationManager;

    /**
     * @var WorkspacesAwareRecordService
     */
    protected static $recordService;

    /**
     * @param FluxService $configurationService
     * @return void
     */
    public function injectConfigurationService(FluxService $configurationService)
    {
        static::$configurationService = $configurationService;
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        static::$configurationManager = $configurationManager;
    }

    /**
     * @param WorkspacesAwareRecordService $recordService
     * @return void
     */
    public function injectRecordService(WorkspacesAwareRecordService $recordService)
    {
        static::$recordService = $recordService;
    }
    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('area', 'string', 'Name of the area to render');
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
        if (!static::$configurationService || !static::$configurationManager || !static::$recordService) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            if (static::$configurationService === null) {
                static::$configurationService = $objectManager->get(FluxService::class);
            }
            if (static::$configurationManager === null) {
                static::$configurationManager = $objectManager->get(ConfigurationManagerInterface::class);
            }
            if (static::$recordService === null) {
                static::$recordService = $objectManager->get(WorkspacesAwareRecordService::class);
            }
        }
        $templateVariableContainer = $renderingContext->getTemplateVariableContainer();
        $contentObjectRenderer = static::getContentObjectRenderer();

        $loadRegister = false;
        if (empty($arguments['loadRegister']) === false) {
            $contentObjectRenderer->cObjGetSingle('LOAD_REGISTER', $arguments['loadRegister']);
            $loadRegister = true;
        }
        $record = $templateVariableContainer->get('record');
        $id = $record['uid'];
        $order = $arguments['order'];
        $area = $arguments['area'];
        $limit = $arguments['limit'] ? $arguments['limit'] : 99999;
        $offset = intval($arguments['offset']);
        $sortDirection = $arguments['sortDirection'];
        $order .= ' ' . $sortDirection;

        if ($GLOBALS['BE_USER']->workspace) {
            $placeholder = BackendUtility::getMovePlaceholder('tt_content', $record['uid']);
            if ($placeholder) {
                // Use the move placeholder if one exists, ensuring that "pid" and "tx_flux_parent" values are taken
                // from the workspace-only placeholder.
                $record = $placeholder;
            }
        }

        // Always use the $record['uid'] when fetching child rows, and fetch everything with same parent and column.
        // The RECORDS function called in getRenderedRecords will handle overlay, access restrictions, time etc.
        // Depending on the TYPO3 setting config.sys_language_overlay, the $record could be either one of the
        // localized version or default version.
        $conditions = sprintf(
            "(tx_flux_parent = '%s' AND tx_flux_column = '%s' AND pid = %d AND colPos = 18181) %s %s",
            $id,
            $area,
            $record['pid'],
            $contentObjectRenderer->enableFields('tt_content'),
            BackendUtility::versioningPlaceholderClause('tt_content')
        );
        $rows = static::$recordService->get('tt_content', '*', $conditions, '', $order, $offset . ',' . $limit);

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
            array_push($elements, static::$configurationManager->getContentObject()->cObjGetSingle('RECORDS', $conf));
        }
        return $elements;
    }
}
