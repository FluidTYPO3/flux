<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\ViewHelpers\Form\DataViewHelper;

class AccessibleDataViewHelper extends DataViewHelper
{
    public static function setFluxService(?FluxService $fluxService): void
    {
        static::$configurationService = $fluxService;
    }

    public static function setRecordService(?WorkspacesAwareRecordService $recordService): void
    {
        static::$recordService = $recordService;
    }
}
