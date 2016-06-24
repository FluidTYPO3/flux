<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * None
 */
class None extends AbstractFormField implements FieldInterface
{

    /**
     * @var integer
     */
    protected $size = 12;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('none');
        $configuration['size'] = $this->getSize();
        return $configuration;
    }

    /**
     * @param integer $size
     * @return None
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }
}
