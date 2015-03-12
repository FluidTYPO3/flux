<?php
namespace FluidTYPO3\Flux\ViewHelpers\Form;

/*
 * This file is part of the FluidTYPO3/Fluidbackend project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Backend\Form\FormEngine;
use TYPO3\CMS\Fluid\ViewHelpers\FormViewHelper as FluidFormViewHelper;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Form;

/**
 * ## Main form rendering ViewHelper
 *
 * Use to render a Flux form as HTML.
 *
 * @package Fluidbackend
 * @subpackage ViewHelpers
 */
class RenderViewHelper extends FluidFormViewHelper {

	/**
	 * @var FluxService
	 */
	protected $configurationService;

	/**
	 * @param FluxService $configurationService
	 * @return void
	 */
	public function injectConfigurationService(FluxService $configurationService) {
		$this->configurationService = $configurationService;
	}

	/**
	 * @param Form $form
	 * @return string
	 */
	public function render(Form $form) {
		$record = $form->getOption(Form::OPTION_RECORD);
		$table = $form->getOption(Form::OPTION_RECORD_TABLE);
		$field = $form->getOption(Form::OPTION_RECORD_FIELD);
		$formHandler = $this->getFormEngine();
		return $formHandler->printNeededJSFunctions_top() .
			$formHandler->getSoloField($table, $record, $field) .
			$formHandler->printNeededJSFunctions();
	}

	/**
	 * @codeCoverageIgnore
	 * @return FormEngine
	 */
	protected function getFormEngine() {
		$formHandler = new FormEngine();
		$formHandler->prependFormFieldNames = $this->getFieldNamePrefix() . '[settings]';
		return $formHandler;
	}

}
