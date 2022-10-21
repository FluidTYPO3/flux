<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * FormInterface
 */
interface FormInterface
{
    /**
     * @return array
     */
    public function build();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $transform
     * @return FormInterface
     */
    public function setTransform($transform);

    /**
     * @return string
     */
    public function getTransform();

    /**
     * @return boolean
     */
    public function getEnabled();

    /**
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $localLanguageFileRelativePath
     * @return $this
     */
    public function setLocalLanguageFileRelativePath($localLanguageFileRelativePath);

    /**
     * @return string
     */
    public function getLocalLanguageFileRelativePath();


    /**
     * @param boolean $disableLocalLanguageLabels
     * @return $this
     */
    public function setDisableLocalLanguageLabels($disableLocalLanguageLabels);

    /**
     * @return boolean
     */
    public function getDisableLocalLanguageLabels();

    /**
     * @param ContainerInterface|FieldInterface|null $parent
     * @return $this
     */
    public function setParent($parent);

    /**
     * @return ContainerInterface
     */
    public function getParent();

    /**
     * @param array $variables
     * @return $this
     */
    public function setVariables($variables);

    /**
     * @return array
     */
    public function getVariables();

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setVariable($name, $value);

    /**
     * @param string $name
     * @return mixed
     */
    public function getVariable($name);

    /**
     * @return ContainerInterface
     */
    public function getRoot();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @param string $extensionName
     * @return $this
     */
    public function setExtensionName($extensionName);

    /**
     * @return mixed
     */
    public function getExtensionName();

    /**
     * @param string $type
     * @return boolean
     */
    public function isChildOfType($type);

    /**
     * @return boolean
     */
    public function hasChildren();

    /**
     * @template T
     * @param class-string<T> $type
     * @param string $name
     * @param string $label
     * @return T
     */
    public function createField($type, $name, $label = null);

    /**
     * @template T
     * @param class-string<T> $type
     * @param string $name
     * @param string $label
     * @return T
     */
    public function createContainer($type, $name, $label = null);

    /**
     * @template T
     * @param class-string<T> $type
     * @param string $name
     * @param string $label
     * @return T
     */
    public function createWizard($type, $name, $label = null);

    /**
     * @param boolean $inherit
     * @return $this
     */
    public function setInherit($inherit);

    /**
     * @return boolean
     */
    public function getInherit();

    /**
     * @param boolean $inheritEmpty
     * @return $this
     */
    public function setInheritEmpty($inheritEmpty);

    /**
     * @return boolean
     */
    public function getInheritEmpty();

    /**
     * Modifies the current Form Component by changing any properties
     * that were passed in $structure. If a component supports special
     * indices in $structure (for example a "fields" property) then
     * that component may specify its own `modify()` method and manually
     * process each of the specially supported keywords.
     *
     * For example, the AbstractFormContainer supports passing "fields"
     * and each field is then attempted fetched from children. If not
     * found, it is created (and the structure passed to the `create()`
     * function which uses the same structure syntax). If it already
     * exists, the `modify()` method is called on that object to trigger
     * the recursive modification of all child components.
     *
     * @param array $structure
     * @return $this
     */
    public function modify(array $structure);
}
