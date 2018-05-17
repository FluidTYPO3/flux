<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Utility;

use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;

/**
 * Compatibility proxy class to access ApplicationContext with a
 * one-shot function call. Required while TYPO3 8.7 LTS is supported.
 */
abstract class ContextUtility
{
    public static function getApplicationContext(): ApplicationContext
    {
        if (class_exists(Environment::class) && method_exists(Environment::class, 'getContext')) {
            return Environment::getContext();
        }
        return Bootstrap::getInstance()->getApplicationContext();
    }
}
