<?php

use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Core;

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
    public function access()
    {
        return true;
    }

    /**
     * @return stringcolPos
     */
    public function main()
    {

        $content = '';
        $wrongConfigFound = false;
        $wrongTemplates = '';

        $configurationService = GeneralUtility::makeInstance(FluxService::class);
        $detectedProviders = $configurationService->resolveConfigurationProviders(
            'tt_content',
            null,
            null
        );

        foreach ($detectedProviders as $provider) {
            $grid = $provider->getGrid(Array(0=>0));
            if (true === empty($grid)) {
                continue;
            }
            $gridConfiguration = $grid->build();
            foreach ($gridConfiguration['rows'] as $row) {
                foreach ($row['columns'] as $column) {
                    if (!array_key_exists('colPos', $column) || $column['colPos'] < 0) {
                        $wrongConfigFound = true;
                        $wrongTemplates .= $provider->getTemplatePathAndFilename($row) . PHP_EOL;
                    }
                }
            }
        }

        if ($wrongConfigFound){
            $content .= 'Please change "name" attribute to "colPos" AND "area" to "column" in following templates:'. PHP_EOL;
            $content .= $wrongTemplates;
        }


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
