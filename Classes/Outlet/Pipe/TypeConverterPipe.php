<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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

use TYPO3\CMS\Extbase\Property\TypeConverterInterface;
use TYPO3\CMS\Extbase\Validation\Error;

/**
 * Standard Input Pipe
 *
 * Accepts POST array form data and uses a Flux Form
 * to perform pre-saving steps (validation, transformation etc).
 *
 * @package Flux
 * @subpackage Outlet
 */
class TypeConverterPipe extends AbstractPipe implements PipeInterface {

	/**
	 * @var TypeConverterInterface
	 */
	protected $typeConverter;

	/**
	 * @var string
	 */
	protected $targetType;

	/**
	 * @param TypeConverterInterface $typeConverter
	 * @return TypeConverterPipe
	 */
	public function setTypeConverter(TypeConverterInterface $typeConverter) {
		$this->typeConverter = $typeConverter;
		return $this;
	}

	/**
	 * @return TypeConverterInterface
	 */
	public function getTypeConverter() {
		return $this->typeConverter;
	}

	/**
	 * @param string $targetType
	 * @return TypeConverterPipe
	 */
	public function setTargetType($targetType) {
		$this->targetType = $targetType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTargetType() {
		return $this->targetType;
	}

	/**
	 * @param mixed $data
	 * @return mixed
	 * @throws Exception
	 */
	public function conduct($data) {
		$targetType = $this->getTargetType();
		$typeConverter = $this->getTypeConverter();
		if (FALSE === $typeConverter->canConvertFrom($data, $targetType)) {
			throw new Exception('TypeConverter ' . get_class($typeConverter) . ' cannot convert ' . gettype($data) . ' to ' . $targetType, 1386292424);
		}
		$output = $this->typeConverter->convertFrom($data, $targetType);
		if (TRUE === $output instanceof Error) {
			throw new Exception('Conversion of ' . gettype($data) . ' to ' . $targetType . ' was unsuccessful, Error was: ' . $output->getMessage(), $output->getCode());
		}
		return $output;
	}

}
