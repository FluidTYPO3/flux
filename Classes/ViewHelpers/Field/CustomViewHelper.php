<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Custom;
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
	 * @return \Closure
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
