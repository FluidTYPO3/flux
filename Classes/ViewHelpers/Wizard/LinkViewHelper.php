<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Link;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Link
 *
 * #### Link input field with link wizard
 *
 *     <flux:field.input name="link">
 *         <flux:wizard.link/>
 *     </flux:field.input>
 */
class LinkViewHelper extends AbstractWizardViewHelper {

	/**
	 * @var string
	 */
	protected $label = 'Select link';

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('activeTab', 'string', 'Active tab of the link popup', FALSE, 'file');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 500);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 500);
		$this->registerArgument('allowedExtensions', 'string', 'Comma-separated list of extensions that are allowed to be selected. Default is all types.', FALSE, FALSE);
		$this->registerArgument('blindLinkOptions', 'string', 'Blind link options', FALSE, '');
		$this->registerArgument('blindLinkFields', 'string', 'Blind link fields', FALSE, '');
	}

	/**
	 * @param RenderingContextInterface $renderingContext
	 * @param array $arguments
	 * @return Link
	 */
	public static function getComponent(RenderingContextInterface $renderingContext, array $arguments) {
		/** @var Link $component */
		$component = static::getPreparedComponent('Link', $renderingContext, $arguments);
		$component->setActiveTab($arguments['activeTab']);
		$component->setWidth($arguments['width']);
		$component->setHeight($arguments['height']);
		$component->setAllowedExtensions($arguments['allowedExtensions']);
		$component->setBlindLinkOptions($arguments['blindLinkOptions']);
		$component->setBlindLinkFields($arguments['blindLinkFields']);
		return $component;
	}

}
