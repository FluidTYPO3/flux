<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\ViewHelpers\Form\RenderViewHelper;

class AccessibleFormRenderViewHelper extends RenderViewHelper
{
    protected static function convertXmlToArray(string $xml): array
    {
        return ['data' => 'foobar'];
    }
}
