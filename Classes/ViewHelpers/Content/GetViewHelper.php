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
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * ### Content: Get ViewHelper
 *
 * Gets all child content of a record based on area.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class GetViewHelper extends AbstractViewHelper {

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var ConfigurationManagerInterface
	 */
	protected $configurationManager;

	/**
	 * @var WorkspacesAwareRecordService
	 */
	protected $recordService;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param ConfigurationManagerInterface $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

	/**
	 * @param WorkspacesAwareRecordService $recordService
	 * @return void
	 */
	public function injectRecordService(WorkspacesAwareRecordService $recordService) {
		$this->recordService = $recordService;
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
	 * @return mixed
	 */
	public function render() {
		$loadRegister = FALSE;
		if (empty($this->arguments['loadRegister']) === FALSE) {
			$this->configurationManager->getContentObject()->cObjGetSingle('LOAD_REGISTER', $this->arguments['loadRegister']);
			$loadRegister = TRUE;
		}
		$record = $this->templateVariableContainer->get('record');
		$id = $record['uid'];
		$order = $this->arguments['order'];
		$area = $this->arguments['area'];
		$limit = $this->arguments['limit'] ? $this->arguments['limit'] : 99999;
		$offset = intval($this->arguments['offset']);
		$sortDirection = $this->arguments['sortDirection'];
		$order .= ' ' . $sortDirection;
		// Always use the $record['uid'] when fetching child rows, and fetch everything with same parent and column.
		// The RECORDS function called in getRenderedRecords will handle overlay, access restrictions, time etc.
		// Depending on the TYPO3 setting config.sys_language_overlay, the $record could be either one of the localized version or default version.
		$conditions = "(tx_flux_parent = '" . $id . "' AND tx_flux_column = '" . $area . "' AND pid = '" . $record['pid'] . "')" .
			$GLOBALS['TSFE']->cObj->enableFields('tt_content');
		$rows = $this->recordService->get('tt_content', '*', $conditions, '', $order, $offset . ',' . $limit);

		$elements = FALSE === (boolean) $this->arguments['render'] ? $rows : $this->getRenderedRecords($rows);
		if (TRUE === empty($this->arguments['as'])) {
			$content = $elements;
		} else {
			$as = $this->arguments['as'];
			if (TRUE === $this->templateVariableContainer->exists($as)) {
				$backup = $this->templateVariableContainer->get($as);
				$this->templateVariableContainer->remove($as);
			}
			$this->templateVariableContainer->add($as, $elements);
			$content = $this->renderChildren();
			$this->templateVariableContainer->remove($as);
			if (TRUE === isset($backup)) {
				$this->templateVariableContainer->add($as, $backup);
			}
		}
		if ($loadRegister) {
			$this->configurationManager->getContentObject()->cObjGetSingle('RESTORE_REGISTER', '');
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
	protected function getRenderedRecords($rows) {
		$elements = array();
		foreach ($rows as $row) {
			$conf = array(
				'tables' => 'tt_content',
				'source' => $row['uid'],
				'dontCheckPid' => 1
			);
			array_push($elements, $this->configurationManager->getContentObject()->cObjGetSingle('RECORDS', $conf));
		}
		return $elements;
	}

}
