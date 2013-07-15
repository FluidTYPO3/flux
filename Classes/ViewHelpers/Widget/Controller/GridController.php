<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Grid Widget Controller
 *
 * @package Flux
 * @subpackage ViewHelpers/Widget
 */
class Tx_Flux_ViewHelpers_Widget_Controller_GridController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $grid = array();

	/**
	 * @var array
	 */
	protected $row = array();

	/**
	 * @param Tx_Flux_Form_Container_Grid $grid
	 * @return void
	 */
	public function setGrid(Tx_Flux_Form_Container_Grid $grid) {
		$this->grid = $grid;
	}

	/**
	 * @param array $row
	 * @return void
	 */
	public function setRow($row) {
		$this->row = $row;
	}

	/**
	 * @return string
	 */
	public function indexAction() {
		$this->view->assign('grid', $this->grid);
		$this->view->assign('row', $this->row);
		$paths = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
		if (TRUE === isset($paths['plugin.']['tx_flux.']['view.']['templateRootPath'])) {
			$templateRootPath = $paths['plugin.']['tx_flux.']['view.']['templateRootPath'];
		} else {
			$templateRootPath = t3lib_extMgm::extPath('flux', 'Resources/Private/Templates');
		}
		if ('/' !== substr($templateRootPath, -1)) {
			$templateRootPath .= '/';
		}
		$templatePathAndFilename = $templateRootPath . 'ViewHelpers/Widget/Grid/Index.html';
		if (TRUE === Tx_Flux_Utility_Version::assertExtensionVersionIsAtLeastVersion('gridelements', 2)) {
			$templatePathAndFilename = $templateRootPath . 'ViewHelpers/Widget/Grid/GridElements.html';
		} elseif (TRUE === Tx_Flux_Utility_Version::assertCoreVersionIsBelowSixPointZero()) {
			$templatePathAndFilename = $templateRootPath . 'ViewHelpers/Widget/Grid/Legacy.html';
		}
		$templatePathAndFilename = t3lib_div::getFileAbsFileName($templatePathAndFilename);
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
	}
}
