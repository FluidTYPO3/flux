<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractFieldViewHelper;

/**
 * FlexForm field section ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class SectionViewHelper extends AbstractFieldViewHelper {

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('name', 'string', 'Name of the attribute, FlexForm XML-valid tag name string', TRUE);
		$this->registerArgument('label', 'string', 'Label for section, can be LLL: value. Optional - if not specified, ' .
			'Flux tries to detect an LLL label named "flux.fluxFormId.sections.foobar" based on section name, in scope of ' .
			'extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
		$this->registerArgument('inherit', 'boolean', 'If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider', FALSE, FALSE);
		$this->registerArgument('inheritEmpty', 'boolean', 'If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider', FALSE, FALSE);
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$container = $this->getContainer();
		/** @var Section $section */
		$section = $container->createContainer('Section', $this->arguments['name'], $this->arguments['label']);
		$section->setExtensionName($this->getExtensionName());
		$section->setVariables($this->arguments['variables']);
		$section->setInherit($this->arguments['inherit']);
		$section->setInheritEmpty($this->arguments['inheritEmpty']);
		$this->setContainer($section);
		$this->renderChildren();
		$this->setContainer($container);
	}

}
