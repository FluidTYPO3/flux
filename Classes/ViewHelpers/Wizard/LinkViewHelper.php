<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Link;

/**
 * Field Wizard: Link
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
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
	 * @return Link
	 */
	public function getComponent() {
		/** @var Link $component */
		$component = $this->getPreparedComponent('Link');
		$component->setActiveTab($this->arguments['activeTab']);
		$component->setWidth($this->arguments['width']);
		$component->setHeight($this->arguments['height']);
		$component->setAllowedExtensions($this->arguments['allowedExtensions']);
		$component->setBlindLinkOptions($this->arguments['blindLinkOptions']);
		$component->setBlindLinkFields($this->arguments['blindLinkFields']);
		return $component;
	}

}
