<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Builder\FlexFormBuilder;
use TYPO3\CMS\Core\Configuration\Event\BeforeFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeFlexFormDataStructureParsedEventListener
{
    public function applyFluxFlexFormDataStructure(BeforeFlexFormDataStructureParsedEvent $event): void
    {
        /** @var FlexFormBuilder $flexFormBuilder */
        $flexFormBuilder = GeneralUtility::makeInstance(FlexFormBuilder::class);
        $structure = $flexFormBuilder->parseDataStructureByIdentifier($event->getIdentifier());
        if (!empty($structure)) {
            $event->setDataStructure($structure);
        }
    }
}
