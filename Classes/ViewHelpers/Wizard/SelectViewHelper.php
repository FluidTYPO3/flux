<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Select;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Edit
 */
class SelectViewHelper extends AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('mode', 'string', 'Selection mode - substitution, append or prepend', FALSE, 'substitution');
		$this->registerArgument('items', 'mixed', 'Comma-separated, comma-and-semicolon-separated or array list of possible values', TRUE);
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return Select
	 */
	public static function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		/** @var Select $component */
		$component = static::getPreparedComponent('Select', $renderingContext, $arguments);
		$component->setMode($arguments['mode']);
		$component->setItems($arguments['items']);
		return $component;
	}

}
