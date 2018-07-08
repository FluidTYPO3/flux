<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Hooks;

class ContentUsedDecision
{
    public function isContentElementUsed(array $parameters)
    {
        // TODO: Temporary override saying everything is in use. Is currently of utmost importance since it prevents infinite recursion by using the page layout view
        return true;
    }
}
