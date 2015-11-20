<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\ColorPicker;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Color Picker
 */
class ColorPickerViewHelper extends AbstractWizardViewHelper {

	/**
	 * @var string
	 */
	protected $label = 'Choose color';

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('dim', 'string', 'Dimensions (WxH, e.g. 20x20) of color picker', FALSE, '20x20');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 450);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 720);
		$this->registerArgument('exampleImg', 'string', 'Example image from which to pick colors', FALSE, 'EXT:flux/Resources/Public/Icons/ColorWheel.png');
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return ColorPicker
	 */
	public static function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		/** @var ColorPicker $component */
		$component = static::getPreparedComponent('ColorPicker', $renderingContext, $arguments);
		$component->setIcon($arguments['exampleImg']);
		$component->setDimensions($arguments['dim']);
		$component->setWidth($arguments['width']);
		$component->setHeight($arguments['height']);
		return $component;
	}

}
