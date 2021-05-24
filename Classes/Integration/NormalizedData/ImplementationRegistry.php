<?php
namespace FluidTYPO3\Flux\Integration\NormalizedData;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImplementationRegistry
{
    protected static $implementations = [];

    /**
     * Register an instance of an implementation, adding it
     * to the registry (to be thawed once requested). Can be
     * provided with a settings array that is passed once to
     * the implementation during instantiation.
     *
     * @param string $implementationClassName
     * @param array $settings
     * @return void
     */
    public static function registerImplementation(string $implementationClassName, array $settings = []): void
    {
        foreach (static::$implementations as $implementationData) {
            list ($registeredClassName, $registeredSettings) = $implementationData;
            if ($registeredClassName === $implementationClassName && $registeredSettings == $settings) {
                return;
            }
        }
        static::$implementations[] = [$implementationClassName, $settings];
    }

    /**
     * Resolves a set of Implementation instances which apply
     * to the table, field and record provided in arguments.
     *
     * @param string $table
     * @param string $field
     * @param array $record
     * @return ImplementationInterface[]
     */
    public static function resolveImplementations(string $table, string $field, array $record): iterable
    {
        $implementations = [];
        foreach (static::$implementations as $implementationData) {
            /** @var ImplementationInterface $instance */
            if (count($implementationData) === 3) {
                list (, , $instance) = $implementationData;
            } else {
                list ($registeredClassName, $registeredSettings) = $implementationData;
                $instance = GeneralUtility::makeInstance($registeredClassName, $registeredSettings);
            }
            if ($field === null && $instance->appliesToTable($table)) {
                $implementations[] = $instance;
            } elseif ($instance->appliesToTableField($table, $field) && $instance->appliesToRecord($record)) {
                $implementations[] = $instance;
            }
        }
        return $implementations;
    }
}
