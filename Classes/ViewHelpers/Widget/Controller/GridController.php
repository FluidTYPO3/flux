<?php
namespace FluidTYPO3\Flux\ViewHelpers\Widget\Controller;
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
 ***************************************************************/

use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * Grid Widget Controller
 *
 * @package Flux
 * @subpackage ViewHelpers/Widget
 */
class GridController extends AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $grid = array();

	/**
	 * @var array
	 */
	protected $row = array();

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Grid $grid
	 * @return void
	 */
	public function setGrid(Grid $grid) {
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
		$paths = $this->configurationService->getViewConfigurationForExtensionName('flux');
		$templateRootPath = TRUE === isset($paths['templateRootPath']) ? $paths['templateRootPath'] : NULL;
		$templatePathAndFilename = ResolveUtility::resolveWidgetTemplateFileBasedOnTemplateRootPathAndEnvironment($templateRootPath);
		$this->view->setTemplatePathAndFilename($templatePathAndFilename);
	}

}
