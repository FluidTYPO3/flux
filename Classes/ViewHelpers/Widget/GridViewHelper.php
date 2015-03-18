<?php
namespace FluidTYPO3\Flux\ViewHelpers\Widget;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * DEPRECATED
 * Grid Widget for rendering a grid in previews of BE elements
 *
 * @package Flux
 * @subpackage ViewHelpers/Widget
 */
class GridViewHelper extends AbstractViewHelper {

	/**
	 * @return string
	 */
	public function render() {
		GeneralUtility::logDeprecatedFunction();
		return '';
	}

}
