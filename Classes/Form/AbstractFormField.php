<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Container\Section;
use FluidTYPO3\Flux\UserFunction\ClearValueWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * AbstractFormField
 */
abstract class AbstractFormField extends AbstractFormComponent implements FieldInterface
{

    /**
     * @var boolean
     */
    protected $required = false;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * Display condition - see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond
     *
     * @var string
     */
    protected $displayCondition = null;

    /**
     * @var boolean
     */
    protected $requestUpdate = false;

    /**
     * @var boolean
     */
    protected $inherit = true;

    /**
     * @var boolean
     */
    protected $inheritEmpty = false;

    /**
     * @var boolean
     */
    protected $clearable = false;

    /**
     * @var boolean
     */
    protected $exclude = true;

    /**
     * @var boolean
     * @deprecated To be removed in next major release
     */
    protected $enable = true;

    /**
     * @var string
     */
    protected $validate;

    /**
     * @var \SplObjectStorage
     */
    protected $wizards;

    /**
     * CONSTRUCTOR
     */
    public function __construct()
    {
        $this->wizards = new \SplObjectStorage();
    }

    /**
     * @param array $settings
     * @return FieldInterface
     * @throws \RuntimeException
     */
    public static function create(array $settings = [])
    {
        if ('Section' === $settings['type']) {
            return Section::create($settings);
        } else {
            $prefix = AbstractFormComponent::NAMESPACE_FIELD . '\\';
            $type = $settings['type'];
            $className = str_replace('/', '\\', $type);
            $className = true === class_exists($prefix . $className) ? $prefix . $className : $className;
        }
        if (false === class_exists($className)) {
            $className = $settings['type'];
        }
        if (false === class_exists($className)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid class- or type-name used in type of field "%s"; "%s" is invalid',
                    $settings['name'],
                    $className
                ),
                1375373527
            );
        }
        /** @var FormInterface $object */
        $object = GeneralUtility::makeInstance(ObjectManager::class)->get($className);
        foreach ($settings as $settingName => $settingValue) {
            $setterMethodName = 'set' . ucfirst($settingName);
            if (true === method_exists($object, $setterMethodName)) {
                call_user_func_array([$object, $setterMethodName], [$settingValue]);
            }
        }
        return $object;
    }

    /**
     * @param string $type
     * @param string $name
     * @param string $label
     * @return WizardInterface
     */
    public function createWizard($type, $name, $label = null)
    {
        $wizard = parent::createWizard($type, $name, $label);
        $this->add($wizard);
        return $wizard;
    }

    /**
     * @param WizardInterface $wizard
     * @return FieldInterface
     */
    public function add(WizardInterface $wizard)
    {
        if (false === $this->wizards->contains($wizard)) {
            $this->wizards->attach($wizard);
            $wizard->setParent($this);
        }
        return $this;
    }

    /**
     * @param string $wizardName
     * @return WizardInterface|false
     */
    public function get($wizardName)
    {
        foreach ($this->wizards as $wizard) {
            if ($wizardName === $wizard->getName()) {
                return $wizard;
            }
        }
        return false;
    }

    /**
     * @param mixed $childOrChildName
     * @return boolean
     */
    public function has($childOrChildName)
    {
        $name = ($childOrChildName instanceof FormInterface) ? $childOrChildName->getName() : $childOrChildName;
        return (false !== $this->get($name));
    }

    /**
     * @param string $wizardName
     * @return WizardInterface|FALSE
     */
    public function remove($wizardName)
    {
        foreach ($this->wizards as $wizard) {
            if ($wizardName === $wizard->getName()) {
                $this->wizards->detach($wizard);
                $this->wizards->rewind();
                $wizard->setParent(null);
                return $wizard;
            }
        }
        return false;
    }

    /**
     * Creates a TCEforms configuration array based on the
     * configuration stored in this ViewHelper. Calls the
     * expected-to-be-overridden stub method getConfiguration()
     * to return the TCE field configuration - see that method
     * for information about how to implement that method.
     *
     * @return array
     */
    public function build()
    {
        if (false === $this->getEnable()) {
            return [];
        }
        $configuration = $this->buildConfiguration();
        $fieldStructureArray = [
            'label' => $this->getLabel(),
            'exclude' => intval($this->getExclude()),
            'config' => $configuration,
            'displayCond' => $this->getDisplayCondition()
        ];
        if (true === isset($configuration['defaultExtras'])) {
            $fieldStructureArray['defaultExtras'] = $configuration['defaultExtras'];
            unset($fieldStructureArray['config']['defaultExtras']);
        }
        $wizards = $this->buildChildren($this->wizards);
        if (true === $this->getClearable()) {
            array_push($wizards, [
                'type' => 'userFunc',
                'userFunc' => ClearValueWizard::class . '->renderField',
                'params' => [
                    'itemName' => $this->getName(),
                ],
            ]);
        }
        $fieldStructureArray['config']['wizards'] = $wizards;
        if (true === $this->getRequestUpdate()) {
            $fieldStructureArray['onChange'] = 'reload';
        }
        return $fieldStructureArray;
    }

    /**
     * @param string $type
     * @return array
     */
    protected function prepareConfiguration($type)
    {
        $fieldConfiguration = [
            'type' => $type,
            'transform' => $this->getTransform(),
            'default' => $this->getDefault(),
        ];
        return $fieldConfiguration;
    }

    /**
     * @param boolean $required
     * @return FieldInterface
     */
    public function setRequired($required)
    {
        $this->required = (boolean) $required;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequired()
    {
        return (boolean) $this->required;
    }

    /**
     * @param mixed $default
     * @return FieldInterface
     */
    public function setDefault($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param string $displayCondition
     * @return FieldInterface
     */
    public function setDisplayCondition($displayCondition)
    {
        $this->displayCondition = $displayCondition;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayCondition()
    {
        return $this->displayCondition;
    }

    /**
     * @param boolean $requestUpdate
     * @return FieldInterface
     */
    public function setRequestUpdate($requestUpdate)
    {
        $this->requestUpdate = (boolean) $requestUpdate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRequestUpdate()
    {
        return (boolean) $this->requestUpdate;
    }

    /**
     * @param boolean $exclude
     * @return FieldInterface
     */
    public function setExclude($exclude)
    {
        $this->exclude = (boolean) $exclude;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getExclude()
    {
        return (boolean) $this->exclude;
    }

    /**
     * @param boolean $enable
     * @return FieldInterface
     */
    public function setEnable($enable)
    {
        GeneralUtility::logDeprecatedFunction();
        $this->enable = (boolean) $enable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnable()
    {
        GeneralUtility::logDeprecatedFunction();
        return (boolean) $this->enable;
    }

    /**
     * @param string $validate
     * @return FieldInterface
     */
    public function setValidate($validate)
    {
        $this->validate = $validate;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidate()
    {
        if (false === (boolean) $this->getRequired()) {
            $validate = $this->validate;
        } else {
            if (true === empty($this->validate)) {
                $validate = 'required';
            } else {
                $validators = GeneralUtility::trimExplode(',', $this->validate);
                array_push($validators, 'required');
                $validate = implode(',', $validators);
            }
        }
        return $validate;
    }

    /**
     * @param boolean $clearable
     * @return FieldInterface
     */
    public function setClearable($clearable)
    {
        $this->clearable = (boolean) $clearable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getClearable()
    {
        return (boolean) $this->clearable;
    }

    /**
     * @return boolean
     */
    public function hasChildren()
    {
        return 0 < $this->wizards->count();
    }

    /**
     * @param array $structure
     * @return ContainerInterface
     */
    public function modify(array $structure)
    {
        if (isset($structure['wizards']) || isset($structure['children'])) {
            $data = isset($structure['children']) ? $structure['children'] : $structure['wizards'];
            foreach ((array) $data as $index => $wizardData) {
                $wizardName = true === isset($wizardData['name']) ? $wizardData['name'] : $index;
                // check if field already exists - if it does, modify it. If it does not, create it.
                if (true === $this->has($wizardName)) {
                    $field = $this->get($wizardName);
                } else {
                    $wizardType = true === isset($wizardData['type']) ? $wizardData['type'] : 'None';
                    $field = $this->createWizard($wizardType, $wizardName);
                }
                $field->modify($wizardData);
            }
            unset($structure['children'], $structure['wizards']);
        }
        return parent::modify($structure);
    }
}
