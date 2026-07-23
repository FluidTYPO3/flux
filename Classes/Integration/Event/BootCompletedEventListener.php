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
        if (empty($GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'])) {
            // Special case: on TYPO3v14, running "composer install" triggers the TYPO3 CLI command "asset:publish"
            // which in turn triggers the BootCompletedEvent which this class listens for. This means that TYPO3 may
            // trigger this event in cases where a site hasn't yet been configured (indicated by not having any DB
            // connection configuration) and this makes Flux fail to process TCA data.
            // So in order to avoid this we check if there is a proper DB connection confíguration before Flux attempts
            // to further process the content type registrations etc.
            return;
        }
        /** @var SpooledConfigurationApplicator $applicator */
        $applicator = GeneralUtility::makeInstance(SpooledConfigurationApplicator::class);
        $applicator->processData();
    }
}
