<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * @param string $type
	 * @return WizardInterface
	 */
	protected function getPreparedComponent($type) {
		$name = (TRUE === isset($this->arguments['name']) ? $this->arguments['name'] : 'wizard');
		$component = $this->getContainer()->createWizard($type, $name);
		$component->setExtensionName($this->getExtensionName());
		$component->setHideParent($this->arguments['hideParent']);
		$component->setLabel($this->arguments['label']);
		$component->setVariables($this->arguments['variables']);
		return $component;
	}

}
