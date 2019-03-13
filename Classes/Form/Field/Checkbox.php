<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;

/**
 * Checkbox
 */
class Checkbox extends AbstractFormField
{

    /**
     * @var string
     */
    protected $renderType = 'checkboxToggle';

    /**
     * @return string
     */
    public function getRenderType()
    {
        return $this->renderType;
    }

    /**
     * @param string $renderType
     * @return Checkbox
     */
    public function setRenderType($renderType)
    {
        $this->renderType = $renderType;
        return $this;
    }

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $fieldConfiguration = $this->prepareConfiguration('check');
        return $fieldConfiguration;
    }

    /**
     * @param string $type
     * @return array
     */
    public function prepareConfiguration($type)
    {
        $configuration = parent::prepareConfiguration($type);
        $configuration['renderType'] = $this->getRenderType();
        return $configuration;
    }
}
