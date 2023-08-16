<?php
namespace FluidTYPO3\Flux\Integration\HookSubscribers;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use TYPO3\CMS\Core\Database\TableConfigurationPostProcessingHookInterface;

/**
 * @codeCoverageIgnore
 */
class TableConfigurationPostProcessor implements TableConfigurationPostProcessingHookInterface
{
    private SpooledConfigurationApplicator $applicator;

    public function __construct(SpooledConfigurationApplicator $applicator)
    {
        $this->applicator = $applicator;
    }

    public function processData(): void
    {
        $this->applicator->processData();
    }
}
