<?php
namespace FluidTYPO3\Flux\Form;

/**
 * Generic TCA field
 */
class Field extends AbstractFormField
{
    /**
     * @var string
     */
    protected $type = 'input';

    /**
     * @var string|null
     */
    protected $displayCond;

    /**
     * @var string|null
     */
    protected $onChange;

    public function buildConfiguration()
    {
        // void, not required by generic field type
    }

    /**
     * @param array $settings
     * @return FieldInterface
     * @throws \RuntimeException
     */
    public static function create(array $settings = [])
    {
        $settings['config']['type'] = $settings['config']['type'] ?? $settings['type'];
        $settings['displayCondition'] = $settings['displayCond'];
        unset($settings['type'], $settings['displayCond']);

        $field = new static();
        foreach ($settings as $propertyName => $value) {
            $setterMethodName = 'set' . ucfirst($propertyName);
            $field->$setterMethodName($value);
        }
        return $field;
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
        $filterClosure = function($value) {
            return $value !== null && $value !== '';
        };

        $fieldStructureArray = [
            'label' => $this->getLabel(),
            'exclude' => intval($this->getExclude()),
            'config' => array_filter($this->getConfig(), $filterClosure),
            'displayCond' => $this->getDisplayCondition(),
            'onChange' => $this->getOnChange(),
        ];

        $fieldStructureArray = array_filter($fieldStructureArray, $filterClosure);
        return $fieldStructureArray;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOnChange(): ?string
    {
        return $this->onChange;
    }

    public function setOnChange(?string $onChange): void
    {
        $this->onChange = $onChange;
    }
}
