<?php
namespace FluidTYPO3\Flux\Form\Container;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormContainer;
use FluidTYPO3\Flux\Form\ContainerInterface;

/**
 * Column
 */
class Column extends AbstractFormContainer implements ContainerInterface
{

    /**
     * @var integer
     */
    protected $columnPosition = 0;

    /**
     * @var integer
     */
    protected $colspan = 1;

    /**
     * @var integer
     */
    protected $rowspan = 1;

    /**
     * @var string
     */
    protected $style = null;

    /**
     * @return array
     */
    public function build()
    {
        $structure = [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'colspan' => $this->getColspan(),
            'rowspan' => $this->getRowspan(),
            'style' => $this->getStyle(),
            'colPos' => $this->getColumnPosition()
        ];
        return $structure;
    }

    /**
     * @param integer $colspan
     * @return Column
     */
    public function setColspan($colspan)
    {
        $this->colspan = $colspan;
        return $this;
    }

    /**
     * @return integer
     */
    public function getColspan()
    {
        return $this->colspan;
    }

    /**
     * @param integer $columnPosition
     * @return Column
     */
    public function setColumnPosition($columnPosition)
    {
        $this->columnPosition = (integer) $columnPosition;
        return $this;
    }

    /**
     * @return integer
     */
    public function getColumnPosition()
    {
        return $this->columnPosition;
    }

    /**
     * @param integer $rowspan
     * @return Column
     */
    public function setRowspan($rowspan)
    {
        $this->rowspan = $rowspan;
        return $this;
    }

    /**
     * @return integer
     */
    public function getRowspan()
    {
        return $this->rowspan;
    }

    /**
     * @param string $style
     * @return Column
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }
}
