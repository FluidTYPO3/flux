<?php
namespace FluidTYPO3\Flux\Form;

use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    public function buildConfiguration(): array
    {
        // void, not required by generic field type
        return [];
    }

    public static function create(array $settings = []): FieldInterface
    {
        if (!isset($settings['config']['type']) && !isset($settings['type'])) {
            throw new \UnexpectedValueException(
                'Field construction requires at least a "type", defined either as "type" or "config.type" property',
                1667227598
            );
        }
        $settings['config']['type'] = $settings['config']['type'] ?? $settings['type'];
        $settings['displayCondition'] = $settings['displayCond'] ?? null;
        unset($settings['type'], $settings['displayCond']);

        /** @var FieldInterface $field */
        $field = GeneralUtility::makeInstance(static::class);
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
     */
    public function build(): array
    {
        $filterClosure = function ($value) {
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

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOnChange(): ?string
    {
        return $this->onChange;
    }

    public function setOnChange(?string $onChange): self
    {
        $this->onChange = $onChange;

        return $this;
    }
}
