<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\Form\Transformation\FormDataTransformer;
use FluidTYPO3\Flux\Provider\ProviderResolver;
use FluidTYPO3\Flux\Service\WorkspacesAwareRecordService;
use FluidTYPO3\Flux\ViewHelpers\Form\DataViewHelper;

class AccessibleDataViewHelper extends DataViewHelper
{
    public static function setFormDataTransformer(?FormDataTransformer $formDataTransformer): void
    {
        static::$formDataTransformer = $formDataTransformer;
    }

    public static function setProviderResolver(?ProviderResolver $providerResolver): void
    {
        static::$providerResolver = $providerResolver;
    }

    public static function setRecordService(?WorkspacesAwareRecordService $recordService): void
    {
        static::$recordService = $recordService;
    }
}
