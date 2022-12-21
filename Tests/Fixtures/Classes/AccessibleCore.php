<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Core;

class AccessibleCore extends Core
{
    protected static function getAbsolutePathForFilename(string $filename): string
    {
        return $filename;
    }

    public static function resetQueuedRegistrations(): void
    {
        static::$queuedContentTypeRegistrations = [];
        static::$extensions = [self::CONTROLLER_ALL => []];
    }

    public static function setRegisteredProviders(array $providers): void
    {
        static::$providers = $providers;
    }
}
