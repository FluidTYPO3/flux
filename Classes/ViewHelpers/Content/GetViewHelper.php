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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ### Content: Get ViewHelper
 *
 * Gets all child content of a record based on area.
 */
class GetViewHelper extends AbstractViewHelper {

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
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		self::$configurationService = $configurationService;
	}

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		self::$configurationManager = $configurationManager;
	}

	/**
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		self::$recordService = $recordService;
	}
	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('area', 'string', 'Name of the area to render');
		$this->registerArgument('limit', 'integer', 'Optional limit to the number of content elements to render');
		$this->registerArgument('offset', 'integer', 'Optional offset to the limit', FALSE, 0);
		$this->registerArgument('order', 'string', 'Optional sort order of content elements - RAND() supported', FALSE, 'sorting');
		$this->registerArgument('sortDirection', 'string', 'Optional sort direction of content elements', FALSE, 'ASC');
		$this->registerArgument('as', 'string', 'Variable name to register, then render child content and insert all results as an array of records', FALSE);
		$this->registerArgument('loadRegister', 'array', 'List of LOAD_REGISTER variable');
		$this->registerArgument('render', 'boolean', 'Optional returning variable as original table rows', FALSE, TRUE);
	}

	/**
	 * Render
	 *
	 * @return string
	 */
	public function render() {
		return static::renderStatic(
			$this->arguments,
			$this->buildRenderChildrenClosure(),
			$this->renderingContext
		);
	}

	/**
	 * Default implementation for use in compiled templates
	 *
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return mixed
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		if (self::$configurationService === NULL || self::$configurationManager === NULL || self::$recordService === NULL) {
			$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
			if (self::$configurationService === NULL) {
				self::$configurationService = $objectManager->get('FluidTYPO3\Flux\Service\FluxService');
			}
			if (self::$configurationManager === NULL) {
				self::$configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
			}
			if (self::$recordService === NULL) {
				self::$recordService = $objectManager->get('FluidTYPO3\Flux\Service\WorkspacesAwareRecordService');
			}
		}
		$templateVariableContainer = $renderingContext->getTemplateVariableContainer();

		$loadRegister = FALSE;
		if (empty($arguments['loadRegister']) === FALSE) {
			self::$configurationManager->getContentObject()->cObjGetSingle('LOAD_REGISTER', $arguments['loadRegister']);
			$loadRegister = TRUE;
		}
		$record = $templateVariableContainer->get('record');
		$id = $record['uid'];
		$order = $arguments['order'];
		$area = $arguments['area'];
		$limit = $arguments['limit'] ? $arguments['limit'] : 99999;
		$offset = intval($arguments['offset']);
		$sortDirection = $arguments['sortDirection'];
		$order .= ' ' . $sortDirection;
		// Always use the $record['uid'] when fetching child rows, and fetch everything with same parent and column.
		// The RECORDS function called in getRenderedRecords will handle overlay, access restrictions, time etc.
		// Depending on the TYPO3 setting config.sys_language_overlay, the $record could be either one of the localized version or default version.
		$conditions = "(tx_flux_parent = '" . $id . "' AND tx_flux_column = '" . $area . "' AND pid = '" . $record['pid'] . "')" .
			$GLOBALS['TSFE']->cObj->enableFields('tt_content');
		$rows = self::$recordService->get('tt_content', '*', $conditions, '', $order, $offset . ',' . $limit);

		$elements = FALSE === (boolean) $arguments['render'] ? $rows : self::getRenderedRecords($rows);
		if (TRUE === empty($arguments['as'])) {
			$content = $elements;
		} else {
			$as = $arguments['as'];
			if (TRUE === $templateVariableContainer->exists($as)) {
				$backup = $templateVariableContainer->get($as);
				$templateVariableContainer->remove($as);
			}
			$templateVariableContainer->add($as, $elements);
			$content = $renderChildrenClosure();
			$templateVariableContainer->remove($as);
			if (TRUE === isset($backup)) {
				$templateVariableContainer->add($as, $backup);
			}
		}
		if ($loadRegister) {
			self::$configurationManager->getContentObject()->cObjGetSingle('RESTORE_REGISTER', '');
		}
		return $content;
	}


	/**
	 * This function renders an array of tt_content record into an array of rendered content
	 * it returns a list of elements rendered by typoscript RECORDS function
	 *
	 * @param array $rows database rows of records (each item is a tt_content table record)
	 * @return array
	 */
	protected static function getRenderedRecords($rows) {
		$elements = array();
		foreach ($rows as $row) {
			$conf = array(
				'tables' => 'tt_content',
				'source' => $row['uid'],
				'dontCheckPid' => 1
			);
			array_push($elements, self::$configurationManager->getContentObject()->cObjGetSingle('RECORDS', $conf));
		}
		return $elements;
	}

}
