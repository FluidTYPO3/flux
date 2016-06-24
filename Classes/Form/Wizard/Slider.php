<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * Slider wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 */
class Slider extends AbstractWizard
{

    /**
     * @var string
     */
    protected $name = 'slider';

    /**
     * @var string
     */
    protected $type = 'slider';

    /**
     * @var string
     */
    protected $icon = null;

    /**
     * @var array
     */
    protected $module = null;

    /**
     * @var integer
     */
    protected $width = 400;

    /**
     * @var integer
     */
    protected $step;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = [
            'width' => $this->getWidth(),
            'step' => $this->getStep(),
        ];
        return $configuration;
    }

    /**
     * @param integer $step
     * @return Slider
     */
    public function setStep($step)
    {
        $this->step = $step;
        return $this;
    }

    /**
     * @return integer
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param integer $width
     * @return Slider
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }
}
