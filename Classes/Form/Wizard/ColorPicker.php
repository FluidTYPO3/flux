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
 * ColorPicker wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 */
class ColorPicker extends AbstractWizard
{

    /**
     * @var string
     */
    protected $name = 'color';

    /**
     * @var string
     */
    protected $type = 'script';

    /**
     * @var string
     */
    protected $icon = 'EXT:flux/Resources/Public/Icons/ColorWheel.png';

    /**
     * @var array
     */
    protected $module = [
        'name' => 'wizard_colorpicker',
    ];

    /**
     * @var string
     */
    protected $dimensions = '20x20';

    /**
     * @var integer
     */
    protected $width = 450;

    /**
     * @var integer
     */
    protected $height = 720;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = [
            'type' => 'colorbox',
            'title' => $this->getLabel(),
            'hideParent' => intval($this->getHideParent()),
            'dim' => $this->getDimensions(),
            'exampleImg' => $this->getIcon(),
            'JSopenParams' => sprintf(
                'height=%d,width=%d,status=0,menubar=0,scrollbars=1',
                $this->getHeight(),
                $this->getWidth()
            )
        ];
        return $configuration;
    }

    /**
     * @param string $dimensions
     * @return ColorPicker
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    /**
     * @return string
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param integer $height
     * @return ColorPicker
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param string $icon
     * @return ColorPicker
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param integer $width
     * @return ColorPicker
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
