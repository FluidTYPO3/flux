<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\NormalizedData;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class ImplementationRegistry
{
    protected static array $implementations = [];

    /**
     * Register an instance of an implementation, adding it
     * to the registry (to be thawed once requested). Can be
     * provided with a settings array that is passed once to
     * the implementation during instantiation.
     */
    public static function registerImplementation(string $implementationClassName, array $settings = []): void
    {
        foreach (static::$implementations as $implementationData) {
            [$registeredClassName, $registeredSettings] = $implementationData;
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
     * @return ImplementationInterface[]
     */
    public static function resolveImplementations(string $table, string $field, array $record): iterable
    {
        $implementations = [];
        foreach (static::$implementations as $implementationData) {
            /** @var class-string<ImplementationInterface> $registeredClassName */
            [$registeredClassName, $registeredSettings] = $implementationData;

            /** @var ImplementationInterface $instance */
            $instance = GeneralUtility::makeInstance($registeredClassName, $registeredSettings);
            if ($instance->appliesToTableField($table, $field) && $instance->appliesToRecord($record)) {
                $implementations[] = $instance;
            }
        }
        return $implementations;
    }
}
