<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * ### Content: Get ViewHelper
 *
 * Gets all child content of a record based on area.
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform
 */
class Tx_Flux_ViewHelpers_Content_GetViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {

	/**
	 * @var Tx_Flux_Service_Content
	 */
	protected $contentService;

	/**
	 * @param Tx_Flux_Service_Content $contentService
	 */
	public function injectContentService(Tx_Flux_Service_Content $contentService) {
		$this->contentService = $contentService;
	}

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('area', 'string', 'Name of the area to render');
		$this->registerArgument('limit', 'integer', 'Optional limit to the number of content elements to render');
		$this->registerArgument('order', 'string', 'Optional sort order of content elements - RAND() supported', FALSE, 'sorting');
		$this->registerArgument('sortDirection', 'string', 'Optional sort direction of content elements', FALSE, 'ASC');
		$this->registerArgument('as', 'string', 'Variable name to register, then render child content and insert all results as an array of records', FALSE);
		$this->registerArgument('loadRegister', 'array', 'List of LOAD_REGISTER variable');
	}

	/**
	 * Render
	 *
	 * @return mixed
	 */
	public function render() {
		$loadRegister = FALSE;
		if (empty($this->arguments['loadRegister']) === FALSE) {
			$this->contentObject->cObjGetSingle('LOAD_REGISTER', $this->arguments['loadRegister']);
			$loadRegister = TRUE;
		}
		$record = $this->templateVariableContainer->get('record');
		$id = $record['uid'];
		$localizedUid = $record['_LOCALIZED_UID'] > 0 ? $record['_LOCALIZED_UID'] : $id;
		$order = $this->arguments['order'];
		$area = $this->arguments['area'];
		$limit = $this->arguments['limit'] ? $this->arguments['limit'] : 99999;
		$sortDirection = $this->arguments['sortDirection'];
		$elements = $this->contentService->getChildContent($localizedUid, $area, $limit, $order, $sortDirection);
		if (FALSE === isset($this->arguments['as'])) {
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
			$this->contentObject->cObjGetSingle('RESTORE_REGISTER', '');
		}
		return $content;
	}

}
