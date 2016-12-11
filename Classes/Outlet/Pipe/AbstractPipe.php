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
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Abstract Pipe
 *
 * Base class for all Pipes
 */
abstract class AbstractPipe implements PipeInterface
{

    /**
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings)
    {
        foreach ($settings as $name => $value) {
            if (true === property_exists($this, $name)) {
                ObjectAccess::setProperty($this, $name, $value);
            }
        }
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function conduct($data)
    {
        return $data;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return substr(lcfirst(array_pop(explode('\\', get_class($this)))), 0, -4);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        $type = $this->getType();
        $translated = LocalizationUtility::translate('pipes.' . $type . '.label', 'flux');
        return (null === $translated ? $type : $translated);
    }

    /**
     * @return FieldInterface[]
     */
    public function getFormFields()
    {
        $class = get_class($this);
        /** @var Input $labelField */
        $labelField = Input::create(['type' => 'Input']);
        $labelField->setName('label');
        $labelField->setDefault($this->getLabel());
        /** @var Select $classField */
        $classField = Select::create(['type' => 'Select']);
        $classField->setName('class');
        $classField->setItems([$class => $class]);
        return [
            'label' => $labelField,
            'class' => $classField
        ];
    }
}
