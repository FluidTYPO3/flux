<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class SelectOption
{
    private bool $namedKeys;
    private string $label;
    private ?string $icon;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string|int|float|null $value
     */
    public function __construct(string $label, $value, ?string $icon = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->icon = $icon;
        $this->namedKeys = version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.3', '>=');
    }

    public function toArray(): array
    {
        $option = [
            'label' => $this->label,
            'value' => $this->value,
        ];
        if ($this->icon !== null) {
            $option['icon'] = null;
        }
        if (!$this->namedKeys) {
            return array_values($option);
        }
        return $option;
    }
}
