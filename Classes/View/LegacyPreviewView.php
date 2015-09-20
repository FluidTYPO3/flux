<?php
namespace FluidTYPO3\Flux\View;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */
use FluidTYPO3\Flux\Form\Container\Column;
use TYPO3\CMS\Backend\View\PageLayoutView;

/**
 * @package Flux
 */
class LegacyPreviewView extends PreviewView {

	/**
	 * @var array
	 */
    protected $templates = array(
        'grid' => '<table cellspacing="0" cellpadding="0" id="content-grid-%s" class="flux-grid%s">
						<tbody>
							%s
						</tbody>
					</table>',
        'gridColumnLegacy' => '<td colspan="%s" rowspan="%s" style="%s">
							<div class="fce-header t3-row-header t3-page-colHeader t3-page-colHeader-label">
								<div>%s</div>
							</div>
							<div class="fce-container t3-page-ce-wrapper">
								<div class="t3-page-ce ui-draggable" data-page="%s">
									<div class="t3-page-ce-dropzone ui-droppable" id="%s" style="min-height: 16px;">
										<div class="t3-page-ce-wrapper-new-ce">
											%s
										</div>
									</div>
								</div>
								%s
							</div>
						</td>',
        'record' => '<div class="t3-page-ce%s %s ui-draggable" id="element-tt_content-%s" data-table="tt_content" data-uid="%s">
						%s
						<div class="t3-page-ce-dropzone ui-droppable"
							 id="colpos-%s-page-%s-%s-after-%s"
							 style="min-height: 16px;">
							<div class="t3-page-ce-wrapper-new-ce">
								%s
							</div>
						</div>
					</div>',
        'gridToggle' => '<div class="grid-visibility-toggle">
							<div class="toggle-content" data-uid="%s">
								<span class="t3-icon t3-icon-actions t3-icon-view-table-%s"></span>
							</div>
							%s
						</div>'
    );


	/**
	 * @param array $row
	 * @param Column $column
	 * @param integer $colPosFluxContent
	 * @param PageLayoutView $dblist
	 * @param integer $target
	 * @param string $id
	 * @param string $content
	 * @return string
	 */
    protected function parseGridColumnTemplate(array $row, Column $column, $colPosFluxContent, $dblist, $target, $id, $content) {
        return sprintf($this->templates['gridColumnLegacy'],
			$column->getColspan(),
			$column->getRowspan(),
			$column->getStyle(),
			$column->getLabel(),
			$target,
			$id,
            $this->drawNewIcon($row, $column) . $this->drawPasteIcon($row, $column) . $this->drawPasteIcon($row, $column, TRUE),
			$content
		);
    }
}
