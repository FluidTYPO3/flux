<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\Container\Column;
use FluidTYPO3\Flux\Form\Container\Container;
use FluidTYPO3\Flux\Form\Container\Grid;
use FluidTYPO3\Flux\Form\Container\Object;
use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\Form\Container\Sheet;
use FluidTYPO3\Flux\Service\FluxService;
use FluidTYPO3\Flux\Utility\ExtensionNamingUtility;
use FluidTYPO3\Flux\Utility\RecursiveArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractFormComponent
 */
abstract class AbstractFormComponent implements FormInterface
{

    const NAMESPACE_FIELD = 'FluidTYPO3\\Flux\\Form\\Field';
    const NAMESPACE_CONTAINER = 'FluidTYPO3\\Flux\\Form\\Container';
    const NAMESPACE_WIZARD = 'FluidTYPO3\\Flux\\Form\\Wizard';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $enabled = true;

    /**
     * @var string
     */
    protected $label = null;

    /**
     * If TRUE, disables LLL label usage and always returns the
     * raw value of $label.
     *
     * @var boolean
     */
    protected $disableLocalLanguageLabels = false;

    /**
     * Relative (from extension $extensionName) path to locallang
     * file containing labels for the LLL values built by this class.
     *
     * @var string
     */
    protected $localLanguageFileRelativePath = Form::DEFAULT_LANGUAGEFILE;

    /**
     * @var string
     */
    protected $extensionName = 'FluidTYPO3.Flux';

    /**
     * @var ContainerInterface
     */
    protected $parent;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @var boolean
     */
    protected $inherit = false;

    /**
     * @var boolean
     */
    protected $inheritEmpty = false;

    /**
     * @var string
     */
    protected $transform;

    /**
     * @param array $settings
     * @return FormInterface
     */
    public static function create(array $settings = [])
    {
        /** @var ObjectManagerInterface $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $className = get_called_class();
        /** @var FormInterface $object */
        $object = $objectManager->get($className);
        return $object->modify($settings);
    }

    /**
     * @param string $type
     * @param string $prefix
     * @return string
     */
    protected function createComponentClassName($type, $prefix)
    {
        $className = str_replace('/', '\\', $type);
        $className = true === class_exists($prefix . '\\' . $className) ? $prefix . '\\' . $className : $className;
        return $className;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $label
     * @return FieldInterface
     */
    public function createField($type, $name, $label = null)
    {
        return $this->createComponent(static::NAMESPACE_FIELD, $type, $name, $label);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $label
     * @return ContainerInterface
     */
    public function createContainer($type, $name, $label = null)
    {
        return $this->createComponent(static::NAMESPACE_CONTAINER, $type, $name, $label);
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $label
     * @return WizardInterface
     */
    public function createWizard($type, $name, $label = null)
    {
        return $this->createComponent(static::NAMESPACE_WIZARD, $type, $name, $label);
    }

    /**
     * @param string $namespace
     * @param string $type
     * @param string $name
     * @param string|NULL $label
     * @return FormInterface
     */
    public function createComponent($namespace, $type, $name, $label = null)
    {
        /** @var FormInterface $component */
        $className = $this->createComponentClassName($type, $namespace);
        $component = $this->getObjectManager()->get($className);
        if (null === $component->getName()) {
            $component->setName($name);
        }
        $component->setLabel($label);
        $component->setLocalLanguageFileRelativePath($this->getLocalLanguageFileRelativePath());
        $component->setDisableLocalLanguageLabels($this->getDisableLocalLanguageLabels());
        $component->setExtensionName($this->getExtensionName());
        return $component;
    }

    /**
     * @param string $transform
     * @return FormInterface
     */
    public function setTransform($transform)
    {
        $this->transform = $transform;
        if ($transform) {
            $root = $this->getRoot();
            if ($root instanceof Form) {
                $root->setOption(Form::OPTION_TRANSFORM, true);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTransform()
    {
        return $this->transform;
    }

    /**
     * @param string $name
     * @return FormInterface
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getEnabled()
    {
        return (boolean) $this->enabled;
    }

    /**
     * @param boolean $enabled
     * @return Form\FormInterface
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (boolean) $enabled;
        return $this;
    }

    /**
     * @param string $extensionName
     * @return FormInterface
     */
    public function setExtensionName($extensionName)
    {
        $this->extensionName = $extensionName;
        return $this;
    }

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return $this->extensionName;
    }

    /**
     * @param string $label
     * @return FormInterface
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        $prefix = '';
        if (true === $this instanceof Sheet) {
            $prefix = 'sheets';
        } elseif (true === $this instanceof Section) {
            $prefix = 'sections';
        } elseif (true === $this instanceof Grid) {
            $prefix = 'grids';
        } elseif (true === $this instanceof Column) {
            $prefix = 'columns';
        } elseif (true === $this instanceof Object) {
            $prefix = 'objects';
        } elseif (true === $this instanceof Container) {
            $prefix = 'containers';
        } elseif (true === $this instanceof FieldInterface) {
            if (true === $this->isChildOfType('Object')) {
                $prefix = 'objects.' . $this->getParent()->getName();
            } else {
                $prefix = 'fields';
            }
        }
        return trim($prefix . '.' . $this->getName(), '.');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->resolveLocalLanguageValueOfLabel($this->label);
    }

    /**
     * @param string $label
     * @param string $path
     * @return NULL|string
     */
    protected function resolveLocalLanguageValueOfLabel($label, $path = null)
    {
        if ($this->getDisableLocalLanguageLabels()) {
            return $label;
        }

        $name = $this->getName();
        $extensionName = $this->extensionName;
        $extensionKey = ExtensionNamingUtility::getExtensionKey($extensionName);
        if (empty($label) && !ExtensionManagementUtility::isLoaded($extensionKey)) {
            return $name;
        } elseif (strpos($label, 'LLL:EXT:') === 0) {
            return $label;
        }

        $relativeFilePath = $this->getLocalLanguageFileRelativePath();
        $relativeFilePath = ltrim($relativeFilePath, '/');
        $filePrefix = 'LLL:EXT:' . $extensionKey . '/' . $relativeFilePath;
        if (strpos($label, 'LLL:') === 0) {
            // Shorthand LLL:name.of.index reference, expand
            list (, $labelIdentifier) = explode(':', $label, 2);
            return $filePrefix . ':' . $labelIdentifier;
        } elseif (!empty($label)) {
            return $label;
        }
        if ($this instanceof Form) {
            return $filePrefix . ':flux.' . $this->getName();
        }
        $root = $this->getRoot();
        $id = $root->getName();
        if (empty($path)) {
            $path = $this->getPath();
        }
        return $filePrefix . ':' . trim('flux.' . $id . '.' . $path, '.');
    }

    /**
     * @param string $localLanguageFileRelativePath
     * @return FormInterface
     */
    public function setLocalLanguageFileRelativePath($localLanguageFileRelativePath)
    {
        $this->localLanguageFileRelativePath = $localLanguageFileRelativePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalLanguageFileRelativePath()
    {
        return $this->localLanguageFileRelativePath;
    }


    /**
     * @param boolean $disableLocalLanguageLabels
     * @return FormInterface
     */
    public function setDisableLocalLanguageLabels($disableLocalLanguageLabels)
    {
        $this->disableLocalLanguageLabels = (boolean) $disableLocalLanguageLabels;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getDisableLocalLanguageLabels()
    {
        return (boolean) $this->disableLocalLanguageLabels;
    }

    /**
     * @param ContainerInterface $parent
     * @return FormInterface
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param array $variables
     * @return FormInterface
     */
    public function setVariables($variables)
    {
        $this->variables = (array) $variables;
        return $this;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return FormInterface
     */
    public function setVariable($name, $value)
    {
        $this->variables = RecursiveArrayUtility::mergeRecursiveOverrule(
            $this->variables,
            RecursiveArrayUtility::convertPathToArray($name, $value)
        );
        return $this;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getVariable($name)
    {
        return ObjectAccess::getPropertyPath($this->variables, $name);
    }

    /**
     * @return ContainerInterface
     */
    public function getRoot()
    {
        $parent = $this->getParent();
        if (null === $parent || $this === $parent) {
            return $this;
        }
        return $parent->getRoot();
    }

    /**
     * @param string $type
     * @return boolean
     */
    public function isChildOfType($type)
    {
        $parent = $this->getParent();
        if ($parent === null) {
            return false;
        }
        return (static::NAMESPACE_CONTAINER . '\\' . $type === get_class($parent) || true === is_a($parent, $type));
    }

    /**
     * @param boolean $inherit
     * @return FormInterface
     */
    public function setInherit($inherit)
    {
        $this->inherit = (boolean) $inherit;
        return $this;
    }

    /**
     * @return integer
     */
    public function getInherit()
    {
        return (boolean) $this->inherit;
    }

    /**
     * @param boolean $inheritEmpty
     * @return FormInterface
     */
    public function setInheritEmpty($inheritEmpty)
    {
        $this->inheritEmpty = (boolean) $inheritEmpty;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getInheritEmpty()
    {
        return (boolean) $this->inheritEmpty;
    }

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
     * @return FormInterface
     */
    public function modify(array $structure)
    {
        if (true === isset($structure['options']) && true === is_array($structure['options'])) {
            foreach ($structure['options'] as $name => $value) {
                $this->setVariable($name, $value);
            }
            unset($structure['options']);
        }
        foreach ($structure as $propertyName => $propertyValue) {
            $setterMethodName = ObjectAccess::buildSetterMethodName($propertyName);
            if (true === method_exists($this, $setterMethodName)) {
                ObjectAccess::setProperty($this, $propertyName, $propertyValue);
            }
        }
        return $this;
    }

    /**
     * @return ObjectManagerInterface
     */
    protected function getObjectManager()
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }

    /**
     * @return FluxService
     */
    protected function getConfigurationService()
    {
        return $this->getObjectManager()->get(FluxService::class);
    }

    /**
     * @param \SplObjectStorage|array $children
     * @return array
     */
    protected function buildChildren($children)
    {
        $structure = [];
        foreach ($children as $child) {
            if (true === (boolean) $child->getEnabled()) {
                $name = $child->getName();
                $structure[$name] = $child->build();
            }
        }
        return $structure;
    }
}
