<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Service\ContentService;
use TYPO3\CMS\Backend\View\PageLayoutView;

/**
 * @package Flux
 */
class PreviewView_7_2 extends PreviewView {

	/**
	 * @param array $row
	 * @param Column $column
	 * @return string
	 */
	protected function drawGridColumn(array $row, Column $column) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;

		$dblist = $this->getInitializedPageLayoutView($row);
		$this->configurePageLayoutViewForLanguageMode($dblist);
		$records = $this->getRecords($dblist, $row, $column->getName());

		$content = '';
		foreach ($records as $record) {
			$content .= $this->drawRecord($row, $column, $record, $dblist);
		}

		$id = 'colpos-' . $colPosFluxContent . '-page-' . $row['pid'] . '--top-' . $row['uid'] . '-' . $column->getName();
		$target = $this->registerTargetContentAreaInSession($row['uid'], $column->getName());

		return <<<CONTENT
		<td colspan="{$column->getColspan()}" rowspan="{$column->getRowspan()}" style="{$column->getStyle()}">
			<div data-colpos="{$colPosFluxContent}" class="t3js-sortable t3js-sortable-lang t3js-sortable-lang-{$dblist->tt_contentConfig['sys_language_uid']} t3-page-ce-wrapper ui-sortable" data-language-uid="{$dblist->tt_contentConfig['sys_language_uid']}">
				<div class="fce-header t3-row-header t3-page-colHeader t3-page-colHeader-label">
					<div>{$column->getLabel()}</div>
				</div>
				<div class="t3-page-ce t3js-page-ce" data-page="{$target}">
					<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="{$id}" style="display: block;">
                        {$this->drawNewIcon($row, $column)}
						{$this->drawPasteIcon($row, $column)}
						{$this->drawPasteIcon($row, $column, TRUE)}
					</div>
					<div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available" ></div>
				</div>
				$content
			</div>
		</td>
CONTENT;
	}

	/**
	 * @param array $parentRow
	 * @param Column $column
	 * @param array $record
	 * @param PageLayoutView $dblist
	 * @return string
	 */
	protected function drawRecord(array $parentRow, Column $column, array $record, PageLayoutView $dblist) {
		$colPosFluxContent = ContentService::COLPOS_FLUXCONTENT;
		$disabledClass = FALSE === empty($record['isDisabled']) ? ' t3-page-ce-hidden' : '';
		$element = $this->drawElement($record, $dblist);
		if (0 === (integer) $dblist->tt_contentConfig['languageMode']) {
			$element = '<div class="t3-page-ce-dragitem">' . $element . '</div>';
		}

		return <<<CONTENT
		<div class="t3-page-ce$disabledClass {$record['_CSSCLASS']} t3js-page-ce t3js-page-ce-sortable" id="element-tt_content-{$record['uid']}" data-table="tt_content" data-uid="{$record['uid']}">
			$element
			<div class="t3js-page-new-ce t3-page-ce-wrapper-new-ce" id="colpos-{$colPosFluxContent}-page-{$parentRow['pid']}-{$parentRow['uid']}-after-{$record['uid']}" style="display: block;">
				{$this->drawNewIcon($parentRow, $column, $record['uid'])}
				{$this->drawPasteIcon($parentRow, $column, FALSE, $record)}
				{$this->drawPasteIcon($parentRow, $column, TRUE, $record)}
			</div>
			<div class="t3-page-ce-dropzone-available t3js-page-ce-dropzone-available"></div>
		</div>
CONTENT;
	}
}
