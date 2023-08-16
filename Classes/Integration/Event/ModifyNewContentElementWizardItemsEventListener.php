<?php
namespace FluidTYPO3\Flux\Integration\Event;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Integration\WizardItemsManipulator;
use TYPO3\CMS\Backend\Controller\Event\ModifyNewContentElementWizardItemsEvent;

class ModifyNewContentElementWizardItemsEventListener
{
    private WizardItemsManipulator $wizardItemsManipulator;

    public function __construct(WizardItemsManipulator $wizardItemsManipulator)
    {
        $this->wizardItemsManipulator = $wizardItemsManipulator;
    }

    public function manipulateWizardItems(ModifyNewContentElementWizardItemsEvent $event): void
    {
        $items = $event->getWizardItems();
        $columnPosition = $event->getColPos();
        $pageInfo = $event->getPageInfo();
        $pageUid = $pageInfo['uid'];
        $event->setWizardItems(
            $this->wizardItemsManipulator->manipulateWizardItems($items, $pageUid, $columnPosition)
        );
    }
}
