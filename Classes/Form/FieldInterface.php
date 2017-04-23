<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * FieldInterface
 */
interface FieldInterface extends FormInterface
{

    /**
     * @return array
     */
    public function buildConfiguration();

    /**
     * @param boolean $clearable
     * @return FieldInterface
     */
    public function setClearable($clearable);

    /**
     * @return boolean
     */
    public function getClearable();

    /**
     * @param boolean $required
     * @return FieldInterface
     */
    public function setRequired($required);

    /**
     * @return boolean
     */
    public function getRequired();

    /**
     * @param mixed $default
     * @return FieldInterface
     */
    public function setDefault($default);

    /**
     * @return mixed
     */
    public function getDefault();

    /**
     * @param string $transform
     * @return FieldInterface
     */
    public function setTransform($transform);

    /**
     * @return string
     */
    public function getTransform();

    /**
     * @param string $displayCondition
     * @return FieldInterface
     */
    public function setDisplayCondition($displayCondition);

    /**
     * @return string
     */
    public function getDisplayCondition();

    /**
     * @param boolean $requestUpdate
     * @return FieldInterface
     */
    public function setRequestUpdate($requestUpdate);

    /**
     * @return boolean
     */
    public function getRequestUpdate();

    /**
     * @param boolean $exclude
     * @return FieldInterface
     */
    public function setExclude($exclude);

    /**
     * @return boolean
     */
    public function getExclude();

    /**
     * @param boolean $enable
     * @return FieldInterface
     * @deprecated To be removed in next major release
     */
    public function setEnable($enable);

    /**
     * @return boolean
     * @deprecated To be removed in next major release
     */
    public function getEnable();

    /**
     * @param string $validate
     * @return FieldInterface
     */
    public function setValidate($validate);

    /**
     * @return string
     */
    public function getValidate();

    /**
     * @param WizardInterface $wizard
     * @return FormInterface
     */
    public function add(WizardInterface $wizard);

    /**
     * @param string $wizardName
     * @return WizardInterface|FALSE
     */
    public function remove($wizardName);
}
