<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;

class AccessibleTableConfigurationPostProcessor extends TableConfigurationPostProcessor
{
    public static function setContentTypeBuilder(?ContentTypeBuilder $contentTypeBuilder): void
    {
        static::$contentTypeBuilder = $contentTypeBuilder;
    }
}
