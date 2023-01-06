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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Table Configuration (TCA) post processor
 *
 * Simply loads the Flux service and lets methods
 * on this Service load necessary configuration.
 *
 * @codeCoverageIgnore
 */
class TableConfigurationPostProcessor implements TableConfigurationPostProcessingHookInterface
{
    protected static bool $recursed = false;

    public function processData(): void
    {
        /** @var SpooledConfigurationApplicator $applicator */
        $applicator = GeneralUtility::makeInstance(SpooledConfigurationApplicator::class);
        $applicator->processData();
    }
}
