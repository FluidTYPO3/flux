<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Claus Due <claus@wildside.dk>, Wildside A/S
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

/**
 * Raw HTML field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Flexform/Field
 */
class Tx_Flux_ViewHelpers_Flexform_Field_HtmlViewHelper extends Tx_Flux_ViewHelpers_Flexform_Field_AbstractFieldViewHelper {

	/**
	 * @return Tx_Fluid_Core_ViewHelper_TemplateVariableContainer
	 */
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}

	/**
	 * Render method
	 * @return void
	 */
	public function render() {
		$this->configuration = $this->renderConfiguration();
		$this->structure = $this->createStructure();
		$this->addField($this->configuration);
	}

	/**
	 * Render method
	 * @return mixed
	 */
	public function renderConfiguration() {
		$self = $this;
		$config = $this->getBaseConfig();
		$config['type'] = 'user';
		$config['userFunc'] = 'EXT:flux/Classes/UserFunction/HtmlOutput.php:Tx_Flux_UserFunction_HtmlOutput->renderField';
		$config['parameters'] = $this->arguments; // will be passed to the UserFunction as argument
		$config['parameters']['closure'] = function($parameters) use ($self) {
			$backupParameters = NULL;
			if ($self->getTemplateVariableContainer()->exists('parameters') === TRUE) {
				$backupParameters = $self->getTemplateVariableContainer()->get('parameters');
				$self->getTemplateVariableContainer()->remove('parameters');
			}
			$self->getTemplateVariableContainer()->add('parameters', $parameters);
			$content = $self->renderChildren();
			$self->getTemplateVariableContainer()->remove('parameters');
			if ($backupParameters !== NULL) {
				$self->getTemplateVariableContainer()->add('parameters', $backupParameters);
			}
			return $content;
		};
		$config['arguments'] = $this->arguments;
		return $config;
	}

}
