<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fluid Template preview renderer for TYPO3 CMS Version > 7.1
 *
 * @package Flux
 * @subpackage Backend
 */
class Preview_7_2 extends Preview {

	/**
	 * @return void
	 */
	protected function attachAssets() {
		if (FALSE === self::$assetsIncluded) {
			$doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
			$doc->backPath = $GLOBALS['BACK_PATH'];

			/** @var PageRenderer $pageRenderer */
			$pageRenderer = $doc->getPageRenderer();
			$pageRenderer->addCssFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/css/grid.css');

			if ((float) substr(TYPO3_version, 0, 3) > 7.1) {
				// /typo3/sysext/backend/Resources/Public/JavaScript/LayoutModule/DragDrop.js
				// is not the perfect solution for Flux Grids!
				// an adapted version of DragDrop.js is used - Resources/Public/js/VersionSevenPointTwo/DragDrop.js
				// Also fluxCollapse.js is updated.
				$fullJsPath = \TYPO3\CMS\Core\Utility\PathUtility::getRelativePath(PATH_typo3, GeneralUtility::getFileAbsFileName('EXT:flux/Resources/Public/js/VersionSevenPointTwo/'));

				// requirejs
				$pageRenderer->addRequireJsConfiguration(array(
					'paths' => array(
						'FluidTypo3/Flux/DragDrop'     => $fullJsPath . 'DragDrop',
					),
				));
				$pageRenderer->loadRequireJsModule('FluidTypo3/Flux/DragDrop');

				// This is necessary for fluxCollapse.js
				$pageRenderer->loadExtJS();

			}

			$pageRenderer->addJsFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/js/fluxCollapse.js');
			self::$assetsIncluded = TRUE;
		}
	}

}
