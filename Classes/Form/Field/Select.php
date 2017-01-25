<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;

/**
 * Select
 */
class Select extends AbstractMultiValueFormField
{

    /**
     * Displays option icons as table beneath the select.
     *
     * @var boolean
     * @see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Select/Index.html#showicontable
     */
    protected $showIconTable = false;

    /**
     * @var string
     */
    protected $renderType = 'selectSingle';

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = parent::prepareConfiguration('select');
        $configuration['showIconTable'] = $this->getShowIconTable();
        return $configuration;
    }

    /**
     * @return boolean
     */
    public function getShowIconTable()
    {
        return $this->showIconTable;
    }

    /**
     * @param boolean $showIconTable
     * @return Select
     */
    public function setShowIconTable($showIconTable)
    {
        $this->showIconTable = $showIconTable;
        return $this;
    }
}
