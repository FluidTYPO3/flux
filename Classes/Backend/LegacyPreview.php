<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Fluid Template preview renderer for TYPO3 CMS Version < 7.1
 *
 * @package Flux
 * @subpackage Backend
 */
class LegacyPreview extends Preview {

    /**
     * @var boolean
     */
    protected static $assetsIncluded = FALSE;

    /**
     * @return void
     */
    protected function attachAssets() {
        if (FALSE === self::$assetsIncluded) {
            $doc = $this->getDocumentTemplate();
            $doc->backPath = $GLOBALS['BACK_PATH'];

            $doc->getPageRenderer()->addCssFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/css/grid.css');
            $doc->getPageRenderer()->addJsFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/js/fluxCollapse.js');
            $doc->getPageRenderer()->addJsFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/js/legacyTypo3pageModule.js');
            self::$assetsIncluded = TRUE;
        }
    }

    /**
     * @return DocumentTemplate
     */
    protected function getDocumentTemplate() {
        return GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
    }

}

