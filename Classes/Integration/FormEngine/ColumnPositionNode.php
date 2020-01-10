<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Form\NodeInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * ColumnPosition
 */
class ColumnPositionNode extends AbstractNode implements NodeInterface
{
    private $parameters = [];

    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        $this->parameters = $data;
    }

    public function render()
    {
        $return = $this->initializeResultArray();
        $return['html'] = GeneralUtility::makeInstance(UserFunctions::class)->renderColumnPositionField($this->parameters['parameterArray']);
        return $return;
    }
}
