<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;
use FluidTYPO3\Flux\Integration\ContentTypeBuilder;
use FluidTYPO3\Flux\Integration\HookSubscribers\TableConfigurationPostProcessor;

class AccessibleRecordBasedContentTypeDefinition extends RecordBasedContentTypeDefinition
{
    public static function setTypes(array $types): void
    {
        static::$types = $types;
    }
}
