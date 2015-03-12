<?php
namespace FluidTYPO3\Flux\ViewHelpers\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Outlet\Pipe\PipeInterface;
use FluidTYPO3\Flux\Outlet\Pipe\TypeConverterPipe;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

/**
 * Type Converter Outlet Pipe ViewHelper
 *
 * Adds a TypeConverterPipe to the Form's Outlet.
 *
 * @package Flux
 * @subpackage ViewHelpers/Form
 */
class TypeConverterViewHelper extends AbstractPipeViewHelper {

	/**
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('targetType', 'string', 'Target type (class name, integer, array, etc.)', TRUE);
		$this->registerArgument(
			'typeConverter', 'mixed',
			'Class or instance of type converter. Can be a short name of a system type converter, minus "Converter" suffix, ' .
				'e.g. PersistentObject, Array etc.',
			TRUE
		);
	}

	/**
	 * @return PipeInterface
	 */
	protected function preparePipeInstance() {
		/** @var TypeConverterPipe $pipe */
		$pipe = $this->objectManager->get('FluidTYPO3\\Flux\\Outlet\\Pipe\\TypeConverterPipe');
		$converter = $this->arguments['typeConverter'];
		if (FALSE === $converter instanceof TypeConverterInterface) {
			if (FALSE === class_exists($converter)) {
				$converter = 'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\' . $converter . 'Converter';
			}
			$converter = $this->objectManager->get($converter);
		}
		$pipe->setTypeConverter($converter);
		$pipe->setTargetType($this->arguments['targetType']);
		return $pipe;
	}

}
