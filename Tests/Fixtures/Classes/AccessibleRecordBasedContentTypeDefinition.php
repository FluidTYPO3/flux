<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Content\TypeDefinition\RecordBased\RecordBasedContentTypeDefinition;

class AccessibleRecordBasedContentTypeDefinition extends RecordBasedContentTypeDefinition
{
    public static function setTypes(array $types): void
    {
        static::$types = $types;
    }
}
