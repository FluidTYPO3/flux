<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColumnPositionNode extends AbstractNode implements NodeInterface
{
    public function render(): array
    {
        $return = $this->initializeResultArray();
        /** @var UserFunctions $userFunctions */
        $userFunctions = GeneralUtility::makeInstance(UserFunctions::class);
        $return['html'] = $userFunctions->renderColumnPositionField($this->data);
        return $return;
    }
}
