<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Input;
use FluidTYPO3\Flux\Form\Field\Select;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

/**
 * Standard Input Pipe
 *
 * Accepts POST array form data and uses a Flux Form
 * to perform pre-saving steps (validation, transformation etc).
 */
class TypeConverterPipe extends AbstractPipe implements PipeInterface {

	/**
	 * @var ObjectManagerInterface
	 */
	protected $objectManager;

	/**
	 * @var TypeConverterInterface
	 */
	protected $typeConverter;

	/**
	 * @var string
	 */
	protected $targetType;

	/**
	 * @param ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}

	/**
	 * @return FieldInterface[]
	 */
	public function getFormFields() {
		$fields = parent::getFormFields();
		$converters = array_values((array) $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['typeConverters']);
		$converters = array_combine($converters, $converters);
		/** @var Select $typeConverter */
		$typeConverter = Select::create(array('type' => 'Select'));
		$typeConverter->setName('typeConverter');
		$typeConverter->setItems($converters);
		$fields['typeConverter'] = $typeConverter;
		$fields['targetType'] = Input::create(array('type' => 'Input'))->setName('targetType');
		return $fields;
	}


	/**
	 * @param TypeConverterInterface|string $typeConverter
	 * @return TypeConverterPipe
	 */
	public function setTypeConverter($typeConverter) {
		if (TRUE === is_string($typeConverter)) {
			$typeConverter = $this->objectManager->get($typeConverter);
		}
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
