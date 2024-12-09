<?php

(function () {
    \FluidTYPO3\Flux\Integration\MultipleItemsProcFunc::register(
        'tt_content',
        'colPos',
        \FluidTYPO3\Flux\Integration\HookSubscribers\ColumnPositionItems::class . '->colPosListItemProcFunc'
    );

    $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['label'] = 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform';

    if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        \FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
    }
})();
