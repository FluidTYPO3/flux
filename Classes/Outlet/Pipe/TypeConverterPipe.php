<?php
namespace FluidTYPO3\Flux\Outlet\Pipe;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Extbase\Error\Error;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Property\TypeConverterInterface;

/**
 * Standard Input Pipe
 *
 * Accepts POST array form data and uses a Flux Form
 * to perform pre-saving steps (validation, transformation etc).
 */
class TypeConverterPipe extends AbstractPipe implements PipeInterface
{

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
     * @var string|null
     */
    protected $propertyName = '';

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function injectObjectManager(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param TypeConverterInterface|string $typeConverter
     * @return TypeConverterPipe
     */
    public function setTypeConverter($typeConverter)
    {
        if (true === is_string($typeConverter)) {
            $typeConverter = $this->objectManager->get($typeConverter);
        }
        $this->typeConverter = $typeConverter;
        return $this;
    }

    /**
     * @return TypeConverterInterface
     */
    public function getTypeConverter()
    {
        return $this->typeConverter;
    }

    /**
     * @param string $targetType
     * @return TypeConverterPipe
     */
    public function setTargetType($targetType)
    {
        $this->targetType = $targetType;
        return $this;
    }

    /**
     * @return string
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function setPropertyName(?string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    /**
     * @param mixed $data
     * @return mixed
     * @throws Exception
     */
    public function conduct($data)
    {
        $output = &$data;
        $subject = &$data;
        if (!empty($this->propertyName)) {
            foreach (explode('.', $this->propertyName) as $segment) {
                $subject = &$subject[$segment];
            }
        }
        $targetType = $this->getTargetType();
        $typeConverter = $this->getTypeConverter();
        if (false === $typeConverter->canConvertFrom($subject, $targetType)) {
            throw new Exception(
                sprintf(
                    'TypeConverter %s cannot convert %s to %s',
                    get_class($typeConverter),
                    gettype($subject),
                    $targetType
                ),
                1386292424
            );
        }
        $subject= $this->typeConverter->convertFrom($subject, $targetType);
        if (true === $output instanceof Error) {
            throw new Exception(
                sprintf(
                    'Conversion of %s to %s was unsuccessful, Error was: %s',
                    gettype($data),
                    $targetType,
                    $output->getMessage()
                ),
                $output->getCode()
            );
        }
        return $output;
    }
}
