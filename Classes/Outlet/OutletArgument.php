<?php
namespace FluidTYPO3\Flux\Outlet;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument as ControllerArgument;
use TYPO3\CMS\Extbase\Mvc\Controller\MvcPropertyMappingConfiguration;
use TYPO3\CMS\Extbase\Property\Exception as PropertyException;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * (Controller) Argument for Outlets
 *
 * Specialised argument class to hold individual arguments for an Outlet.
 */
class OutletArgument
{
    /**
     * @var PropertyMapper
     */
    protected $propertyMapper;

    /**
     * @var MvcPropertyMappingConfiguration
     */
    protected $propertyMappingConfiguration;

    /**
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    /**
     * Name of this argument
     *
     * @var string
     */
    protected $name = '';

    /**
     * Data type of this argument's value
     *
     * @var string
     */
    protected $dataType;

    /**
     * Actual value of this argument
     *
     * @var mixed
     */
    protected $value;

    /**
     * Array of validators which must validate this argument's value
     *
     * @var ValidatorInterface[]
     */
    protected $validators = [];

    /**
     * The validation results. This can be asked if the argument has errors.
     *
     * @var Result
     */
    protected $validationResults;

    /**
     * @param PropertyMapper $propertyMapper
     * @return void
     */
    public function injectPropertyMapper(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * @param MvcPropertyMappingConfiguration $propertyMappingConfiguration
     * @return void
     */
    public function injectPropertyMappingConfiguration(MvcPropertyMappingConfiguration $propertyMappingConfiguration)
    {
        $this->propertyMappingConfiguration = $propertyMappingConfiguration;
    }

    /**
     * @param ValidatorResolver $validatorResolver
     * @return void
     */
    public function injectValidatorResolver(ValidatorResolver  $validatorResolver)
    {
        $this->validatorResolver = $validatorResolver;
    }

    /**
     * Creates a new instance of the OutletArgument based on the name and dataTape this argument will represent
     *
     * @param string $name Name of this argument
     * @param string $dataType The data type of this argument
     * @throws \InvalidArgumentException if $name is not a string or empty
     */
    public function __construct($name, $dataType)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name must be of type string, ' . gettype($name) . ' given.', 1187951688);
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('$name must be a non-empty string.', 1232551853);
        }
        $this->name = $name;
        $this->dataType = TypeHandlingUtility::normalizeType($dataType);
    }

    /**
     * Returns the name of this argument
     *
     * @return string This argument's name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the data type of this argument's value
     *
     * @return string The data type
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Sets custom validators which are used supplementary to the base validation
     *
     * @param ValidatorInterface[] $validators The actual validator object
     * @return ControllerArgument Returns $this (used for fluent interface)
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * Returns the set validators
     *
     * @return ValidatorInterface[] The set validators
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * @param string $type
     * @param array $options
     * @throws NoSuchValidatorException
     */
    public function addValidator($type, array $options = [])
    {
        $validator = $this->validatorResolver->createValidator($type, $options);
        if ($validator === null) {
            throw new NoSuchValidatorException('Unknown Validator: ' . $type, 1478559461);
        }
        $this->validators[] = $validator;
    }

    /**
     * Sets the value of this argument.
     *
     * @param mixed $rawValue The value of this argument
     *
     * @return OutletArgument
     */
    public function setValue($rawValue)
    {
        $this->value = $this->propertyMapper->convert($rawValue, $this->dataType, $this->propertyMappingConfiguration);

        $this->validationResults = $this->propertyMapper->getMessages();
        if (count($this->validators) > 0) {
            $conjunctionValidator = new ConjunctionValidator();
            foreach ($this->validators as $validator) {
                $conjunctionValidator->addValidator($validator);
            }
            $validationMessages = $conjunctionValidator->validate($this->value);
            $this->validationResults->merge($validationMessages);
        }
        return $this;
    }

    /**
     * Returns the value of this argument
     *
     * @return mixed The value of this argument - if none was set, NULL is returned
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Return the Property Mapping Configuration used for this argument; can be used by the initialize*action to modify the Property Mapping.
     *
     * @return MvcPropertyMappingConfiguration
     */
    public function getPropertyMappingConfiguration()
    {
        return $this->propertyMappingConfiguration;
    }

    /**
     * @return boolean TRUE if the argument is valid, FALSE otherwise
     */
    public function isValid()
    {
        if ($this->validationResults === null) {
            return true;
        }
        return !$this->validationResults->hasErrors();
    }

    /**
     * @return Result Validation errors which have occurred.
     */
    public function getValidationResults()
    {
        return $this->validationResults;
    }
}
