<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Hooks;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class HookHandler
{
    const FORM_CREATED = 'formCreated';
    const FORM_BUILT = 'formBuilt';
    const FORM_FETCHED = 'formFetched';
    const FORM_CHILD_ADDED = 'formChildAdded';
    const FORM_CHILD_REMOVED = 'formChildRemoved';
    const FORM_COMPONENT_CREATED = 'formComponentCreated';
    const FORM_COMPONENT_MODIFIED = 'formComponentModified';
    const FORM_DATA_FETCHED = 'formDataFetched';
    const PREVIEW_RENDERED = 'previewRendered';
    const PREVIEW_COLUMN_RENDERED = 'previewColumnRendered';
    const PREVIEW_RECORDS_FETCHED = 'previewRecordsFetched';
    const PREVIEW_RECORD_RENDERED = 'previewRecordRendered';
    const PREVIEW_GRID_RENDERED = 'previewGridRendered';
    const PREVIEW_GRID_TOGGLE_STATUS_FETCHED = 'previewGridToggleStatusFetched';
    const PREVIEW_GRID_TOGGLE_RENDERED = 'previewGridToggleRendered';
    const VALUE_BEFORE_TRANSFORM = 'valueBeforeTransform';
    const VALUE_AFTER_TRANSFORM = 'valueAfterTransform';
    const CONTROLLER_RESOLVED = 'controllerResolved';
    const CONTROLLER_BEFORE_REQUEST = 'controllerBeforeRequest';
    const CONTROLLER_AFTER_REQUEST = 'controllerAfterRequest';
    const CONTROLLER_AFTER_RENDERING = 'controllerAfterRendering';
    const CONTROLLER_VARIABLES_ASSIGNED = 'controllerVariablesAssigned';
    const CONTROLLER_SETTINGS_INITIALIZED = 'controllerSettingsInitialized';
    const CONTROLLER_VIEW_INITIALIZED = 'controllerViewInitialized';
    const PROVIDERS_RESOLVED = 'providersResolved';
    const PROVIDER_RESOLVED_TEMPLATE = 'providerResolvedTemplate';
    const PROVIDER_EXTRACTED_OBJECT = 'providerExtractedObject';
    const PROVIDER_COMMAND_EXECUTED = 'providerCommandExecuted';
    const PROVIDER_REGISTERED = 'providerRegistered';
    const PROVIDER_EXTENSION_REGISTERED = 'providerExtensionRegistered';
    const NESTED_CONTENT_FETCHED = 'nestedContentFetched';
    const NESTED_CONTENT_RENDERED = 'nestedContentRendered';
    const ALLOWED_CONTENT_RULES_FETCHED = 'allowedContentRulesFetched';
    const ALLOWED_CONTENT_FILTERED = 'allowedContentFiltered';
    const CONTENT_TYPE_CONFIGURED = 'contentTypeConfigured';
    const RECORD_MOVED = 'recordMoved';
    const RECORD_CHILD_PLACEHOLDERS_MOVED = 'recordChildPlaceholdersMoved';
    const RECORD_CONTENT_SORTED = 'recordSorted';
    const RECORD_RESOLVED = 'recordResolved';
    const RECORD_INITIALIZED = 'recordInitialized';
    const CACHES_CLEARED = 'cachesCleared';

    /**
     * @var HookSubscriberInterface[][]
     */
    protected static $subscribers = [];

    /**
     * Subscribe to a Flux hook
     *
     * Tells Flux to call "trigger()" on $subscriber when hook
     * $hook gets called. Returns the singleton instance of the
     * subscriber, allowing you to call additional methods to
     * configure the subscriber instance after registration.
     *
     * Note that one hook subscriber instance will be created
     * per hook - unless your hook subscriber implements the
     * singleton interface in which case the same instance is
     * used for every hook.
     *
     * @param string $hook
     * @param string $subscriber
     * @return void
     */
    public static function subscribe(string $hook, string $subscriber)
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][$hook][$subscriber] = $subscriber;
    }

    /**
     * Remove a hook subscriber
     *
     * Removes registration of $subscriber from calls to hook
     * $hook. Returns TRUE if the hook was removed, FALSE if
     * it was not (which implies it was never registered).
     *
     * @param string $hook
     * @param string $subscriber
     * @return bool
     */
    public static function unsubscribe(string $hook, string $subscriber): bool
    {
        $existed = array_key_exists($subscriber, $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][$hook] ?? []);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][$hook][$subscriber]);
        return $existed;
    }

    /**
     * Triggers $hook with $data and returns $data with or
     * without any modifications done by hook subscribers.
     *
     * @param string $hook
     * @param array $data
     * @return array
     */
    public static function trigger(string $hook, array $data = []): array
    {
        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['hooks'][$hook] ?? [] as $subscriberClassName) {
            $data = static::resolveSubscriberInstance($hook, $subscriberClassName)->trigger($hook, $data);
        }
        return $data;
    }

    /**
     * @param string $hook
     * @param string $subscriber
     * @return HookSubscriberInterface
     * @throws \InvalidArgumentException
     */
    protected static function resolveSubscriberInstance(string $hook, string $subscriber): HookSubscriberInterface
    {
        if (!isset(static::$subscribers[$hook][$subscriber])) {
            if (!is_a($subscriber, HookSubscriberInterface::class, true)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Flux hook subscriber "%s" must implement "%s" but the interface was not found on the class',
                        $subscriber,
                        HookSubscriberInterface::class
                    )
                );
            }
            /** @var HookSubscriberInterface $subscriberObject */
            $subscriberObject = GeneralUtility::makeInstance($subscriber);
            static::$subscribers[$hook][$subscriber] = $subscriberObject;
        }
        return static::$subscribers[$hook][$subscriber];
    }
}
