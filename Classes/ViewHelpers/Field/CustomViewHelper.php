<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;
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

use TYPO3\CMS\Fluid\Core\ViewHelper\TemplateVariableContainer;

/**
 * Custom FlexForm field ViewHelper
 *
 * @package Flux
 * @subpackage ViewHelpers/Field
 */
class CustomViewHelper extends UserFuncViewHelper {

	const DEFAULT_USERFUNCTION = 'EXT:flux/Classes/UserFunction/HtmlOutput.php:\FluidTYPO3\Flux\UserFunction\HtmlOutput->renderField';

	/**
	 * Initialize
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->overrideArgument('userFunc', 'string', 'User function to render the Closure built by this ViewHelper', FALSE, self::DEFAULT_USERFUNCTION);
	}

	/**
	 * @return Custom
	 */
	public function getComponent() {
		/** @var Custom $component */
		$component = parent::getComponent('Custom');
		$closure = $this->buildClosure();
		$component->setClosure($closure);
		return $component;
	}

	/**
	 * @return Closure
	 */
	protected function buildClosure() {
		$self = $this;
		$closure = function($parameters) use ($self) {
			$backupParameters = NULL;
			$backupParameters = NULL;
			if ($self->getTemplateVariableContainer()->exists('parameters') === TRUE) {
				$backupParameters = $self->getTemplateVariableContainer()->get('parameters');
				$self->getTemplateVariableContainer()->remove('parameters');
			}
			$self->getTemplateVariableContainer()->add('parameters', $parameters);
			$content = $self->renderChildren();
			$self->getTemplateVariableContainer()->remove('parameters');
			if (NULL !== $backupParameters) {
				$self->getTemplateVariableContainer()->add('parameters', $backupParameters);
			}
			return $content;
		};
		return $closure;
	}

	/**
	 * @return TemplateVariableContainer
	 */
	public function getTemplateVariableContainer() {
		return $this->templateVariableContainer;
	}

}
