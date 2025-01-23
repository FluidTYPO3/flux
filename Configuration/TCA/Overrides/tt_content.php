<?php

(function () {
    \FluidTYPO3\Flux\Integration\MultipleItemsProcFunc::register(
        'tt_content',
        'colPos',
        \FluidTYPO3\Flux\Integration\HookSubscribers\ColumnPositionItems::class . '->colPosListItemProcFunc'
    );

    $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['label'] = 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform';

    // TYPO3v13 neglects to define that the tt_content table should store the UID of the original record when copying
    // a content record to a new page. Since Flux demands this column (the original record's details are looked up in
    // order to determine how to process children), we must define this instruction ourselves.
    $GLOBALS['TCA']['tt_content']['ctrl']['origUid'] = 't3_origuid';

    if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        \FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
    }
})();
