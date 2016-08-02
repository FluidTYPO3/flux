<?php
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class ext_update
 *
 * Performs update tasks for extension flux
 */
 // @codingStandardsIgnoreStart
class ext_update {

	/**
	 * @return boolean
	 */
	public function access() {
		return TRUE;
	}

	/**
	 * @return string
	 */
	public function main() {
		$numAffected = 0;

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos = -42', array('colPos' => 18181));
		$numAffected += $GLOBALS['TYPO3_DB']->sql_affected_rows();

		// clean up any inconsistencies from issue #1125
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'tx_flux_parent != 0 and ((tx_flux_column is null) or (tx_flux_column = \'\'))', array('tx_flux_parent' => 0));
		$numAffected += $GLOBALS['TYPO3_DB']->sql_affected_rows();

		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_reflection');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_reflection_tags');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_object');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_object_tags');

		$msg = '<p>' . $numAffected . ' rows have been updated. System object caches cleared.</p>';

		// search for any disappeared content due to issue #1125
		$inaccessibleRecords = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, pid, CType, header',
			'tt_content',
			'colPos = 18181 AND ((tx_flux_parent IS NULL) OR (tx_flux_parent = 0) OR (tx_flux_column IS NULL) OR (tx_flux_column = \'\')) ' . BackendUtility::deleteClause('tt_content'),
			'',
			'pid ASC, uid ASC'
		);

		if (count($inaccessibleRecords) > 0) {
			$msg .= '<div class="panel panel-warning">';

			$msg .= '<div class="panel-heading">';
			$msg .= 'Inaccessible Content';
			$msg .= '</div class="panel-heading">';

			$msg .= '<div class="panel-body">';
			$msg .= '<p>Some content elements were found to have become inaccessible from backend and most-likely also from frontend. These issues need to be investigated individually and cannot be fixed automatically.</p>';
			$msg .= '<p>To fix these issues, follow the links below to edit the records.</p>';
			$msg .= '<ul>';
			$msg .= '<li>If the content element is still relevant (despite having been inaccessible previously), you can restore it by selecting a column other than "Fluid Content Area" and then saving these changes. Please check its position and appearance immediately afterwards (you may want to hide it before saving).</li>';
			$msg .= '<li>If you are sure you could delete the content (which may not have been updated since it disappeared from backend). Please note that the content may only be a translation of another record. You may choose to delete the record via the edit link.</li>';
			$msg .= '<li>You can re-run the update script for flux at any time to see this information again. It will not show once there are no more affected, not deleted content elements.</li>';
			if (ExtensionManagementUtility::isLoaded('workspaces')) {
				$msg .= '<li>Note that the effect on workspaces has not been tested, be cautious if you are actively relying on that feature.</li>';
			}
			$msg .= '</ul>';
			$msg .= '<p>Affected records:</p>';

			$msg .= '<table class="table table-striped table-hover">';
			$msg .= '<thead>';
			$msg .= '<tr>';
			$msg .= '<th>Content ID</th>';
			$msg .= '<th>Page ID</th>';
			$msg .= '<th>CType</th>';
			$msg .= '<th>Header (if set)</th>';
			$msg .= '<th>Actions</th>';
			$msg .= '</tr>';
			$msg .= '</thead>';
			$msg .= '<tbody>';

			foreach ($inaccessibleRecords as $inaccessibleRecord) {
				$msg .= '<tr>';
				$msg .= '<td>' . htmlspecialchars($inaccessibleRecord['uid']) . '</td>';
				$msg .= '<td>' . htmlspecialchars($inaccessibleRecord['pid']) . '</td>';
				$msg .= '<td>' . htmlspecialchars($inaccessibleRecord['CType']) . '</td>';
				$msg .= '<td>' . htmlspecialchars($inaccessibleRecord['header']) . '</td>';
				$msg .= '<td><div class="btn-group">';
				$iconEditContent = IconUtility::getSpriteIcon('actions-document-open');
				$jsEditContent = BackendUtility::editOnClick('&edit[tt_content][' . (integer) $inaccessibleRecord['uid'] . ']=edit');
				$msg .= '<a class="btn btn-default" href="#" onclick="javascript:' . htmlspecialchars($jsEditContent) . '">' . $iconEditContent . '</a>';
				$msg .= '</div></td>';
				$msg .= '</tr>';
			}

			$msg .= '</tbody>';
			$msg .= '</table>';
			$msg .= '</div>';

			$msg .= '</div>';
		}

		return $msg;
	}
}
