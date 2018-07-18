<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

use FluidTYPO3\Flux\Utility\ColumnNumberUtility;

class ContentUsedDecision
{
    public function isContentElementUsed(array $parameters): bool
    {
        return $parameters['used'] || $parameters['record']['colPos'] >= ColumnNumberUtility::MULTIPLIER;
    }
}
