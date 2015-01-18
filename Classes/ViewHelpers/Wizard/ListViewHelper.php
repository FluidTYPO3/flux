<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\ListWizard;

/**
 * Field Wizard: List
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
class ListViewHelper extends AbstractWizardViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('table', 'string', 'Table name that records are added to', TRUE);
		$this->registerArgument('pid', 'mixed', 'Storage page UID or (as is default) ###CURRENT_PID###', FALSE, '###CURRENT_PID###');
		$this->registerArgument('width', 'integer', 'Width of the popup window', FALSE, 500);
		$this->registerArgument('height', 'integer', 'height of the popup window', FALSE, 500);
	}

	/**
	 * @return ListWizard
	 */
	public function getComponent() {
		/** @var ListWizard $component */
		$component = $this->getPreparedComponent('ListWizard');
		$component->setTable($this->arguments['table']);
		$component->setStoragePageUid($this->arguments['pid']);
		$component->setWidth($this->arguments['width']);
		$component->setHeight($this->arguments['height']);
		return $component;
	}

}
