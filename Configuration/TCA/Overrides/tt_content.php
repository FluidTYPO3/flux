<?php

(function () {
    \FluidTYPO3\Flux\Integration\MultipleItemsProcFunc::register(
        'tt_content',
        'colPos',
        \FluidTYPO3\Flux\Integration\Overrides\BackendLayoutView::class . '->colPosListItemProcFunc'
    );

    $GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['label'] = 'LLL:EXT:flux/Resources/Private/Language/locallang.xlf:tt_content.pi_flexform';

    if (\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::getOption(\FluidTYPO3\Flux\Utility\ExtensionConfigurationUtility::OPTION_FLEXFORM_TO_IRRE)) {
        \FluidTYPO3\Flux\Integration\NormalizedData\FlexFormImplementation::registerForTableAndField('tt_content', 'pi_flexform');
    }

    if (php_sapi_name() === 'cli') {
        /*
         * This is hairy.
         *
         * When you execute a TYPO3 command over CLI, none of the usual boot process is executed. This means that, among
         * other things, the BootCompletedEvent that Flux uses to trigger the registration of content types, never gets
         * executed.
         *
         * Normally this wouldn't be a problem but if you combine this with a setup that also creates instances of
         * TemplateService (directly or indirectly) and allows TemplateService to write to caches (which it will by
         * default) then the following happens:
         *
         * - Flux works normally in frontend, in cold and warm cache.
         * - Nothing breaks by page loads, with or without a BE user present.
         * - But, as soon as a CLI task that uses TemplateService in a way that causes TS to be built with a cache ID
         *   that is the same as an FE request (e.g. when the root line is for a page that's also loaded in FE) then
         *   seriously bad things start happening:
         * - First off, the cache entry is indeed found and returned.
         * - But somehow, TemplateService appears to be unaware of the fact that TS is already cached and decides to
         *   start building and then caching the already-cached TS.
         * - And as described above, this causes the TS to be built incompletely (since any TS that was added via an
         *   event will simply not be present).
         * - TemplateService decides to store this value to cache, which then overrides the already-cached version.
         * - On the next page load in FE, TemplateService retrieves the now incomplete cache entry and uses it as TS.
         * - Causing ContentObjectRenderer to error out with an "Content type XYZ has no rendering definition" error.
         *
         * One extension that will cause this is EXT:solr - but there may be others. EXT:solr uses TemplateService when
         * building an emulated instance of TSFE which is used when resolving Solr "site" configurations. There are
         * probably others, and it is very possible that custom implementations do something similar. We therefore must
         * do something to prevent the problem.
         *
         * Since changing the nature of Flux's usage of the BootCompletedEvent is not possible, and since doing the
         * registration of content types from here (tt_content TCA override file) causes other errors (unable to
         * determine controller for plugin XYZ action BAZ), we are left with one choice:
         *
         * Cheap but consistent detection of CLI context here, and indiscriminately apply Flux's content type setup even
         * though we know this would cause problems in an FE request. This causes TemplateService to correctly build
         * the TypoScript that indiscriminately replaces the already-cached version, thus avoiding that TemplateService
         * caches an incomplete version of TS that would then break subsequent page loads in FE.
         *
         * The side effect is that IF someone ever decides "hey, I want to render an FE plugin in CLI, but I want to do
         * that not by rendering the specific controller action but rather by using Extbase's Bootstrap approach" then
         * that would inevitably fail with an "unable to determine controller for plugin XYZ action BAZ" type error.
         *
         * I judge this risk to be acceptably low enough that this stupid - but very effective - fallback-style loading
         * of Flux's content types when in CLI mode is acceptable.
         */
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator::class)->processData();
    }
})();
