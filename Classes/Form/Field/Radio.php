<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * @package Flux
 * @subpackage Form\Field
 */
class Radio extends Select
{

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = parent::prepareConfiguration('radio');
        $configuration['items'] = $this->getItems();
        return $configuration;
    }
}
