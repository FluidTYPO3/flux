<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Add;

/**
 * AbstractWizard
 */
abstract class AbstractWizard extends AbstractFormComponent implements WizardInterface
{

    /**
     * @var boolean
     */
    protected $hideParent = false;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $icon;

    /**
     * @var array
     */
    protected $module;

    /**
     * @return array
     */
    public function build()
    {
        $structure = [
            'type' => $this->type,
            'title' => $this->getLabel(),
            'icon' => $this->icon,
            'hideParent' => intval($this->getHideParent()),
            'module' => $this->module
        ];
        $configuration = $this->buildConfiguration();
        $structure = array_merge($structure, $configuration);
        return $structure;
    }

    /**
     * @param boolean $hideParent
     * @return Add
     */
    public function setHideParent($hideParent)
    {
        $this->hideParent = $hideParent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getHideParent()
    {
        return $this->hideParent;
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return false;
    }
}
