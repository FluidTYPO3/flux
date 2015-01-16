<?php
namespace FluidTYPO3\Flux\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

use FluidTYPO3\Flux\Form;

/**
 * FlexForm configuration container ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers
 */
class FormViewHelper extends AbstractFormViewHelper {

	/**
	 * Initialize arguments
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument('id', 'string', 'Identifier of this Flexible Content Element, [a-z0-9\-] allowed', TRUE);
		$this->registerArgument('label', 'string', 'Label for the form, can be LLL: value. Optional - if not specified, Flux ' .
			'tries to detect an LLL label named "flux.fluxFormId", in scope of extension rendering the Flux form.', FALSE, NULL);
		$this->registerArgument('description', 'string', 'Short description of the purpose/function of this form', FALSE, NULL);
		$this->registerArgument('icon', 'string', 'DEPRECATED: Use `options="{icon: \'iconreference\'}"` or the `flux:form .option.icon` ViewHelper', FALSE, NULL);
		$this->registerArgument('mergeValues', 'boolean', 'DEPRECATED AND IGNORED. To cause value merging, simly prefix your field names with the table name, e.g. ' .
			'`tt_content.header` will overwrite the "header" column in the record with the FlexForm field value when saving the record if the record belongs in table `tt_content`.', FALSE, FALSE);
		$this->registerArgument('enabled', 'boolean', 'If FALSE, features which use this form can elect to skip it. Respect for this flag depends on the feature using the form.', FALSE, TRUE);
		$this->registerArgument('wizardTab', 'string', 'DEPRECATED: Use `options="{group: \'GroupName\'}` or the `flux:form.option.group` ViewHelper');
		$this->registerArgument('compact', 'boolean', 'If TRUE, disables sheet usage in the form. WARNING! AVOID DYNAMIC VALUES ' .
			'AT ALL COSTS! Toggling this option is DESTRUCTIVE to variables currently saved in the database!', FALSE, FALSE);
		$this->registerArgument('variables', 'array', 'Freestyle variables which become assigned to the resulting Component - ' .
			'can then be read from that Component outside this Fluid template and in other templates using the Form object from this template', FALSE, array());
		$this->registerArgument('options', 'array', 'Custom options to be assigned to Form object - valid values depends on the. See docs of extension ' .
			'in which you use this feature. Can also be set using `flux:form.option` as child of `flux:form`.');
		$this->registerArgument('localLanguageFileRelativePath', 'string', 'Relative (from extension) path to locallang file containing labels for the LLL values used in this form.', FALSE, Form::DEFAULT_LANGUAGEFILE);
		$this->registerArgument('extensionName', 'string', 'If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.');
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		/** @var Form $form */
		$form = $this->objectManager->get('FluidTYPO3\Flux\Form');
		$container = $form->last();
		// configure Form instance
		$form->setId($this->arguments['id']);
		$form->setName($this->arguments['id']);
		$form->setLabel($this->arguments['label']);
		$form->setDescription($this->arguments['description']);
		$form->setEnabled($this->arguments['enabled']);
		$form->setCompact($this->arguments['compact']);
		$extensionName = $this->getExtensionName();
		$form->setExtensionName($extensionName);
		$form->setLocalLanguageFileRelativePath($this->arguments['localLanguageFileRelativePath']);
		$form->setVariables((array) $this->arguments['variables']);
		$form->setOptions((array) $this->arguments['options']);
		if (FALSE === $form->hasOption(Form::OPTION_ICON)) {
			$form->setOption(Form::OPTION_ICON, $this->arguments['icon']);
		}
		if (FALSE === $form->hasOption(Form::OPTION_GROUP)) {
			$form->setOption(Form::OPTION_GROUP, $this->arguments['wizardTab']);
		}
		// rendering child nodes with Form as active container
		$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_FORM, $form);
		$this->templateVariableContainer->add(self::SCOPE_VARIABLE_FORM, $form);
		$this->setContainer($container);
		if (FALSE === $this->hasArgument(self::SCOPE_VARIABLE_EXTENSIONNAME)) {
			$this->renderChildren();
		} else {
			// render with stored extension context, backing up any stored variable from parents.
			$extensionName = NULL;
			if (TRUE === $this->viewHelperVariableContainer->exists(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME)) {
				$extensionName = $this->viewHelperVariableContainer->get(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			}
			$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			$this->renderChildren();
			$this->viewHelperVariableContainer->remove(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			if (NULL !== $extensionName) {
				$this->viewHelperVariableContainer->addOrUpdate(self::SCOPE, self::SCOPE_VARIABLE_EXTENSIONNAME);
			}
		}
		$this->viewHelperVariableContainer->remove(self::SCOPE, self::SCOPE_VARIABLE_CONTAINER);
		$this->templateVariableContainer->remove(self::SCOPE_VARIABLE_CONTAINER);
	}

}
