<?php

/**
 * Class ext_update
 *
 * Performs update tasks for extension flux
 */
 // @codingStandardsIgnoreStart
class ext_update
{

	/**
	 * @return boolean
	 */
	public function access() {
		return true;
	}

	/**
	 * @return string
	 */
	public function main() {
		$content = '';

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'colPos = -42', ['colPos' => 18181]);
		$content .= 'Switch to positive colPos (see #477): ' .
			$GLOBALS['TYPO3_DB']->sql_affected_rows() . ' rows affected' . PHP_EOL;

		// Fix records with wrong references (see #1176)
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tt_content', 'tx_flux_parent > 0 AND tx_flux_column = \'\'', ['tx_flux_parent' => 0]);
		$content .= 'Fix records with wrong references (see #1176): ' .
			$GLOBALS['TYPO3_DB']->sql_affected_rows() . ' rows affected' . PHP_EOL;

		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_reflection');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_reflection_tags');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_object');
		$GLOBALS['TYPO3_DB']->exec_TRUNCATEquery('cf_extbase_object_tags');
		$content .= 'System object caches cleared.' . PHP_EOL;

		return nl2br($content);
	}
}
