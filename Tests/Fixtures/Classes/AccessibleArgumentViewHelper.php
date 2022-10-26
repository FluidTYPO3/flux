<?php

namespace FluidTYPO3\Flux\Tests\Fixtures\Classes;

use FluidTYPO3\Flux\ViewHelpers\Outlet\ArgumentViewHelper;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class AccessibleArgumentViewHelper extends ArgumentViewHelper
{
    public static function setObjectManager(?ObjectManagerInterface $objectManager): void
    {
        static::$objectManager = $objectManager;
    }
}
