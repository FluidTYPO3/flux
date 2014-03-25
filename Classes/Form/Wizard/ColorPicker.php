<?php
namespace FluidTYPO3\Flux\Form\Wizard;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * @package Flux
 * @subpackage Form\Wizard
 */
class ColorPicker extends AbstractWizard {

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
	 * @var string
	 */
	protected $script = 'wizard_colorpicker.php';

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
	public function buildConfiguration() {
		$configuration = array(
			'type' => 'colorbox',
			'title' => $this->getLabel(),
			'script' => $this->script,
			'hideParent' => intval($this->getHideParent()),
			'dim' => $this->getDimensions(),
			'exampleImg' => $this->getIcon(),
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1'
		);
		return $configuration;
	}

	/**
	 * @param string $dimensions
	 * @return ColorPicker
	 */
	public function setDimensions($dimensions) {
		$this->dimensions = $dimensions;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDimensions() {
		return $this->dimensions;
	}

	/**
	 * @param integer $height
	 * @return ColorPicker
	 */
	public function setHeight($height) {
		$this->height = $height;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param string $icon
	 * @return ColorPicker
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getIcon() {
		return $this->icon;
	}

	/**
	 * @param integer $width
	 * @return ColorPicker
	 */
	public function setWidth($width) {
		$this->width = $width;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getWidth() {
		return $this->width;
	}

}
