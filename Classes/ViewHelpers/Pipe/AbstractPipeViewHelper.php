<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\ViewHelpers\AbstractFormViewHelper;

/**
 * Class AbstractPipeViewHelper
 */
abstract class AbstractPipeViewHelper extends AbstractFormViewHelper {

	const DIRECTION_IN = 'in';
	const DIRECTION_OUT = 'out';

	/**
	 * @return void
	 */
	public function initializeArguments() {
		$this->registerArgument(
			'direction', 'string',
			'Which endpoint to attach the Pipe to - either "in" or "out". See documentation about Outlets and Pipes',
			FALSE, self::DIRECTION_OUT
		);
	}

	/**
	 * @return void
	 */
	public function render() {
		$form = $this->getForm();
		$sheet = TRUE === $form->has('pipes') ? $form->get('pipes') : $form->createContainer('Sheet', 'pipes');
		$pipe = $this->preparePipeInstance();
		foreach ($pipe->getFormFields() as $formField) {
			$sheet->add($formField);
		}
		$form->getOutlet()->addPipeOut($pipe);
	}

	/**
	 * @return PipeInterface
	 */
	protected function preparePipeInstance() {
		return $this->objectManager->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\StandardPipe');
	}

}
