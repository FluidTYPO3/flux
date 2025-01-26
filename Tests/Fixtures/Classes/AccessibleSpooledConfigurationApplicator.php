<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Builder\ContentTypeBuilder;
use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;

class AccessibleSpooledConfigurationApplicator extends SpooledConfigurationApplicator
{
    public static function setContentTypeBuilder(?ContentTypeBuilder $contentTypeBuilder): void
    {
        static::$contentTypeBuilder = $contentTypeBuilder;
    }
}
