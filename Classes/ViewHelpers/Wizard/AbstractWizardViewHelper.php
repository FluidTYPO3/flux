<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;
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

use FluidTYPO3\Flux\Form\WizardInterface;
use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Base class for Field Wizard style ViewHelpers
 *
 * @package Flux
 * @subpackage ViewHelpers/Wizard
 */
abstract class AbstractWizardViewHelper extends AbstractFormViewHelper {

	/**
	 * @var string
	 */
	protected $label = NULL;

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('label', 'string', 'Optional title of this Wizard', FALSE, $this->label);
		$this->registerArgument('hideParent', 'boolean', 'If TRUE, hides the parent field', FALSE, FALSE);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
	}

	/**
	 * @return void
	 */
	public function render() {
		$component = $this->getComponent();
		$field = $this->getContainer();
		$field->add($component);
	}

	/**
	 * @param string $type
	 * @return WizardInterface
	 */
	protected function getPreparedComponent($type) {
		/** @var WizardInterface $component */
		$component = $this->objectManager->get('FluidTYPO3\Flux\Form\Wizard\\' . $type);
		$component->setHideParent($this->arguments['hideParent']);
		$component->setLabel($this->arguments['label']);
		$component->setVariables($this->arguments['variables']);
		return $component;
	}

}
