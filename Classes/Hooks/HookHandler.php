<?php
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
    public const string FORM_CREATED = 'formCreated';
    public const string FORM_BUILT = 'formBuilt';
    public const string FORM_FETCHED = 'formFetched';
    public const string FORM_CHILD_ADDED = 'formChildAdded';
    public const string FORM_CHILD_REMOVED = 'formChildRemoved';
    public const string FORM_COMPONENT_CREATED = 'formComponentCreated';
    public const string FORM_COMPONENT_MODIFIED = 'formComponentModified';
    public const string FORM_DATA_FETCHED = 'formDataFetched';
    public const string PREVIEW_RENDERED = 'previewRendered';
    public const string PREVIEW_COLUMN_RENDERED = 'previewColumnRendered';
    public const string PREVIEW_RECORDS_FETCHED = 'previewRecordsFetched';
    public const string PREVIEW_RECORD_RENDERED = 'previewRecordRendered';
    public const string PREVIEW_GRID_RENDERED = 'previewGridRendered';
    public const string PREVIEW_GRID_TOGGLE_STATUS_FETCHED = 'previewGridToggleStatusFetched';
    public const string PREVIEW_GRID_TOGGLE_RENDERED = 'previewGridToggleRendered';
    public const string VALUE_BEFORE_TRANSFORM = 'valueBeforeTransform';
    public const string VALUE_AFTER_TRANSFORM = 'valueAfterTransform';
    public const string CONTROLLER_RESOLVED = 'controllerResolved';
    public const string CONTROLLER_BEFORE_REQUEST = 'controllerBeforeRequest';
    public const string CONTROLLER_AFTER_REQUEST = 'controllerAfterRequest';
    public const string CONTROLLER_AFTER_RENDERING = 'controllerAfterRendering';
    public const string CONTROLLER_VARIABLES_ASSIGNED = 'controllerVariablesAssigned';
    public const string CONTROLLER_SETTINGS_INITIALIZED = 'controllerSettingsInitialized';
    public const string CONTROLLER_VIEW_INITIALIZED = 'controllerViewInitialized';
    public const string PROVIDERS_RESOLVED = 'providersResolved';
    public const string PROVIDER_RESOLVED_TEMPLATE = 'providerResolvedTemplate';
    public const string PROVIDER_EXTRACTED_OBJECT = 'providerExtractedObject';
    public const string PROVIDER_COMMAND_EXECUTED = 'providerCommandExecuted';
    public const string PROVIDER_REGISTERED = 'providerRegistered';
    public const string PROVIDER_EXTENSION_REGISTERED = 'providerExtensionRegistered';
    public const string NESTED_CONTENT_FETCHED = 'nestedContentFetched';
    public const string NESTED_CONTENT_RENDERED = 'nestedContentRendered';
    public const string ALLOWED_CONTENT_RULES_FETCHED = 'allowedContentRulesFetched';
    public const string ALLOWED_CONTENT_FILTERED = 'allowedContentFiltered';
    public const string CONTENT_TYPE_CONFIGURED = 'contentTypeConfigured';
    public const string RECORD_MOVED = 'recordMoved';
    public const string RECORD_CHILD_PLACEHOLDERS_MOVED = 'recordChildPlaceholdersMoved';
    public const string RECORD_CONTENT_SORTED = 'recordSorted';
    public const string RECORD_RESOLVED = 'recordResolved';
    public const string RECORD_INITIALIZED = 'recordInitialized';
    public const string CACHES_CLEARED = 'cachesCleared';

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
