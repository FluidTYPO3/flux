<?php
namespace FluidTYPO3\Flux\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\RecordService;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Fluid Template preview renderer
 *
 * @package Flux
 * @subpackage Backend
 */
class Preview implements PageLayoutViewDrawItemHookInterface {

	/**
	 * @var boolean
	 */
	protected static $assetsIncluded = FALSE;

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @var RecordService
	 */
	protected $recordService;

	/**
	 * @var array
	 */
	public $itemLabels = array();

	/**
	 * CONSTRUCTOR
	 */
	public function __construct() {
		$this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$this->configurationService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		$this->recordService = $this->objectManager->get('FluidTYPO3\Flux\Service\RecordService');
		$this->itemLabels = array();
		foreach ($GLOBALS['TCA']['tt_content']['columns'] as $name => $val) {
			$this->itemLabels[$name] = $this->getLanguageService()->sL($val['label']);
		}

	}

	/**
	 *
	 * @param PageLayoutView $parentObject
	 * @param boolean $drawItem
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @return void
	 */
	public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		$this->renderPreview($headerContent, $itemContent, $row, $drawItem);
		unset($parentObject);
	}

	/**
	 * @param string $headerContent
	 * @param string $itemContent
	 * @param array $row
	 * @param boolean $drawItem
	 * @return NULL
	 */
	public function renderPreview(&$headerContent, &$itemContent, array &$row, &$drawItem) {
		$fieldName = NULL; // every provider for tt_content will be asked to get a preview
		if ('shortcut' === $row['CType'] && FALSE === strpos($row['records'], ',')) {
			$itemContent = $this->createShortcutIcon($row) . $itemContent;
		} else {
			$itemContent = '<a name="c' . $row['uid'] . '"></a>' . $itemContent;
		}
		$providers = $this->configurationService->resolveConfigurationProviders('tt_content', $fieldName, $row);
		foreach ($providers as $provider) {
			/** @var ProviderInterface $provider */
			list ($previewHeader, $previewContent, $continueDrawing) = $provider->getPreview($row);
			if (FALSE === empty($previewHeader)) {
				$headerContent = $previewHeader . (FALSE === empty($headerContent) ? ': ' . $headerContent : '');
				$drawItem = FALSE;
			}
			if (FALSE === empty($previewContent)) {
				$itemContent .= $previewContent;
				$drawItem = FALSE;
			}
			if (FALSE === $continueDrawing) {
				break;
			}
		}
		$footerContentArray = array();
		$this->getProcessedValue('tt_content', 'starttime,endtime,fe_group,spaceBefore,spaceAfter', $row, $footerContentArray);
		if (0 < count($footerContentArray)) {
			$footerContent = '<div class="t3-page-ce-info">'. implode('<br />', $footerContentArray) . '</div>';
		}
		if (FALSE === empty($footerContent)) {
			$itemContent .= '<div class="t3-page-ce-footer">' . $footerContent . '</div>';
		}
		$this->attachAssets();
		return NULL;
	}

	/**
	 * @param array $row
	 * @return string
	 */
	protected function createShortcutIcon($row) {
		$targetRecord = $this->getPageTitleAndPidFromContentUid(intval($row['records']));
		$title = LocalizationUtility::translate('reference', 'Flux', array(
			$targetRecord['title']
		));
		$targetLink = '?id=' . $targetRecord['pid'] . '#c' . $row['records'];
		$iconClass = 't3-icon t3-icon-actions-insert t3-icon-insert-reference t3-icon-actions t3-icon-actions-insert-reference';
		$icon = '<a name="c' . $row['uid'] . '" title="' . $title . '" href="' . $targetLink . '"><span class="' . $iconClass . '"></span></a>';
		return $icon;
	}

	/**
	 * @param integer $contentUid
	 * @return array
	 */
	protected function getPageTitleAndPidFromContentUid($contentUid) {
		return reset($this->recordService->get('tt_content t, pages p', 'p.title, t.pid', "t.uid = '" . $contentUid . "' AND p.uid = t.pid"));
	}

	/**
	 * @return void
	 */
	protected function attachAssets() {
		if (FALSE === self::$assetsIncluded) {
			$doc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
			$doc->backPath = $GLOBALS['BACK_PATH'];

			$doc->getPageRenderer()->addCssFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/css/grid.css');
			$doc->getPageRenderer()->addJsFile($doc->backPath . ExtensionManagementUtility::extRelPath('flux') . 'Resources/Public/js/fluxCollapse.js');
			self::$assetsIncluded = TRUE;
		}
	}

	/**
	 * Creates processed values for all field names in $fieldList based on values from $row array.
	 * The result is 'returned' through $info which is passed as a reference
	 *
	 * @param string $table Table name
	 * @param string $fieldList Comma separated list of fields.
	 * @param array $row Record from which to take values for processing.
	 * @param array $info Array to which the processed values are added.
	 * @return void
	 * @todo Define visibility
	 */
	public function getProcessedValue($table, $fieldList, array $row, array &$info) {
		// Splitting values from $fieldList
		$fieldArr = explode(',', $fieldList);
		// Traverse fields from $fieldList
		foreach ($fieldArr as $field) {
			if ($row[$field]) {
				$info[] = '<strong>' . htmlspecialchars($this->itemLabels[$field]) . '</strong> '
					. htmlspecialchars(BackendUtility::getProcessedValue($table, $field, $row[$field]));
			}
		}
	}

	/**
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

}
