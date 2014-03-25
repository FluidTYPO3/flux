<?php
namespace FluidTYPO3\Flux\ViewHelpers\Content;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\Flux\Service\FluxService;
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
		$localizedUid = $record['_LOCALIZED_UID'] > 0 ? $record['_LOCALIZED_UID'] : $id;
		$order = $this->arguments['order'];
		$area = $this->arguments['area'];
		$limit = $this->arguments['limit'] ? $this->arguments['limit'] : 99999;
		$offset = intval($this->arguments['offset']);
		$sortDirection = $this->arguments['sortDirection'];
		$order .= ' ' . $sortDirection;
		$conditions = "((tx_flux_column = '" . $area . ':' . $localizedUid . "')
			OR (tx_flux_parent = '" . $localizedUid . "' AND (tx_flux_column = '" . $area . "' OR tx_flux_column = '" . $area . ':' . $localizedUid . "')))
			AND deleted = 0 AND hidden = 0";
		$rows = (array) $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'tt_content', $conditions, 'uid', $order, $offset . ',' . $limit);
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
			array_push($elements, $this->configurationManager->getContentObject()->RECORDS($conf));
		}
		return $elements;
	}

}
