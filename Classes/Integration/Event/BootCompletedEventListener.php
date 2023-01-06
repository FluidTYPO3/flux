<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\Configuration\SpooledConfigurationApplicator;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BootCompletedEventListener
{
    public function spoolQueuedTcaOperations(BootCompletedEvent $event): void
    {
        /** @var SpooledConfigurationApplicator $applicator */
        $applicator = GeneralUtility::makeInstance(SpooledConfigurationApplicator::class);
        $applicator->processData();
    }
}
