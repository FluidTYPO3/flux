<?php
declare(strict_types=1);
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

abstract class AbstractFormField extends AbstractFormComponent implements FieldInterface
{
    /**
     * @var mixed
     */
    protected $default;

    /**
     * Display condition - see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond
     *
     * @var array|string|null
     */
    protected $displayCondition = null;

    protected bool $native = false;
    protected bool $required = false;
    protected bool $requestUpdate = false;
    protected bool $inherit = true;
    protected bool $inheritEmpty = false;
    protected bool $clearable = false;
    protected bool $protectable = false;
    protected bool $exclude = false;
    protected ?string $validate = null;
    protected ?string $position = null;
    protected array $config = [];

    public static function create(array $settings = []): FormInterface
    {
        if (!isset($settings['type'])) {
            $settings['type'] = static::class;
        }
        if ('Section' === $settings['type']) {
            return Section::create($settings);
        } else {
            $prefix = AbstractFormComponent::NAMESPACE_FIELD . '\\';
            $type = $settings['type'];
            $className = str_replace('/', '\\', $type);
            $className = class_exists($prefix . $className) ? $prefix . $className : $className;
        }
        if (!class_exists($className)) {
            $className = $settings['type'];
        }
        if (!class_exists($className)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid class- or type-name used in type of field "%s"; "%s" is invalid',
                    $settings['name'] ?? '(unknown)',
                    $className
                ),
                1375373527
            );
        }
        /** @var FieldInterface $object */
        $object = GeneralUtility::makeInstance($className);
        foreach ($settings as $settingName => $settingValue) {
            $setterMethodName = 'set' . ucfirst($settingName);
            if (true === method_exists($object, $setterMethodName)) {
                $object->{$setterMethodName}($settingValue);
            }
        }
        return $object;
    }

    /**
     * Creates a TCEforms configuration array based on the
     * configuration stored in this ViewHelper. Calls the
     * expected-to-be-overridden stub method getConfiguration()
     * to return the TCE field configuration - see that method
     * for information about how to implement that method.
     */
    public function build(): array
    {
        if (!$this->getEnabled()) {
            return [];
        }

        // The "config" section consists of whichever configuration arry the component built, but with
        // priority to any options set directly as raw TCA field config options in $this->config.
        $configuration = array_replace($this->buildConfiguration(), $this->getConfig());
        $filterClosure = function ($value) {
            return $value !== null && $value !== '' && $value !== [];
        };
        $configuration = array_filter($configuration, $filterClosure);
        $fieldStructureArray = [
            'label' => $this->getLabel(),
            'exclude' => intval($this->getExclude()),
            'config' => $configuration
        ];
        if (($displayCondition = $this->getDisplayCondition())) {
            $fieldStructureArray['displayCond'] = $displayCondition;
        }

        if ($this->getClearable()) {
            $fieldStructureArray['config']['fieldWizard']['fluxClearValue'] = [
                'renderType' => 'fluxClearValue',
            ];
        }

        if ($this->getProtectable() && $this->getInherit()) {
            $fieldStructureArray['config']['fieldWizard']['fluxProtectValue'] = [
                'renderType' => 'fluxProtectValue',
            ];
        }

        if ($this->getRequestUpdate()) {
            $fieldStructureArray['onChange'] = 'reload';
        }
        return $fieldStructureArray;
    }

    protected function prepareConfiguration(string $type): array
    {
        return [
            'type' => $type,
            'transform' => $this->getTransform(),
            'default' => $this->getDefault(),
        ];
    }

    public function isNative(): bool
    {
        return $this->native;
    }

    public function setNative(bool $native): self
    {
        $this->native = $native;
        return $this;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): self
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
     * @param string|array|null $displayCondition
     */
    public function setDisplayCondition($displayCondition): self
    {
        $this->displayCondition = $displayCondition;
        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getDisplayCondition()
    {
        return $this->displayCondition;
    }

    public function setRequestUpdate(bool $requestUpdate): self
    {
        $this->requestUpdate = $requestUpdate;
        return $this;
    }

    public function getRequestUpdate(): bool
    {
        return $this->requestUpdate;
    }

    public function setExclude(bool $exclude): self
    {
        $this->exclude = $exclude;
        return $this;
    }

    public function getExclude(): bool
    {
        return $this->exclude;
    }

    public function setValidate(?string $validate): self
    {
        $this->validate = $validate;
        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function getValidate(): ?string
    {
        if (!$this->getRequired()) {
            $validate = $this->validate;
        } else {
            if (empty($this->validate)) {
                $validate = 'required';
            } else {
                $validators = GeneralUtility::trimExplode(',', $this->validate);
                array_push($validators, 'required');
                $validate = implode(',', $validators);
            }
        }
        return $validate;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function setClearable(bool $clearable): self
    {
        $this->clearable = (boolean) $clearable;
        return $this;
    }

    public function getClearable(): bool
    {
        return $this->clearable;
    }

    public function getProtectable(): bool
    {
        return $this->protectable;
    }

    public function setProtectable(bool $protectable): self
    {
        $this->protectable = $protectable;
        return $this;
    }
}
