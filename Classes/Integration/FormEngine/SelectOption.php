<?php
namespace FluidTYPO3\Flux\Integration\FormEngine;

use TYPO3\CMS\Core\Utility\VersionNumberUtility;

class SelectOption
{
    private bool $namedKeys;
    public function __construct(private string $label, private string|int|null $value, private string|null $icon = null)
    {
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
