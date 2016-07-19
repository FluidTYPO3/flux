<?php

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

		return $numAffected . ' rows have been updated. System object caches cleared.';
	}
}
