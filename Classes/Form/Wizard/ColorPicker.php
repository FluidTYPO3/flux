<?php
declare(strict_types=1);
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
 *
 * @deprecated Will be removed in Flux 10.0
 */
class ColorPicker extends AbstractWizard
{
    protected ?string $name = 'color';
    protected ?string $type = 'script';
    protected ?string $icon = 'EXT:flux/Resources/Public/Icons/ColorWheel.png';
    protected array $module = [
        'name' => 'wizard_colorpicker',
    ];
    protected string $dimensions = '20x20';
    protected int $width = 450;
    protected int $height = 720;

    public function buildConfiguration(): array
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

    public function setDimensions(string $dimensions): self
    {
        $this->dimensions = $dimensions;
        return $this;
    }

    public function getDimensions(): string
    {
        return $this->dimensions;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }
}
