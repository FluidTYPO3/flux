<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * Select wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 *
 * @deprecated Will be removed in Flux 10.0
 */
class Select extends AbstractWizard
{
    protected ?string $name = 'select';
    protected ?string $type = 'select';
    protected ?string $icon = 'list.gif';
    protected string $mode = 'substitution';

    /**
     * Comma-separated, comma-and-semicolon-separated or array
     * list of possible values
     *
     * @var \Traversable|string|null
     */
    protected $items;

    public function buildConfiguration(): array
    {
        return [
            'mode' => $this->getMode(),
            'items' => $this->getFormattedItems()
        ];
    }

    /**
     * Builds an array of selector options based on a type of string
     */
    protected function buildItems(string $itemsString): array
    {
        $itemsString = trim($itemsString, ',');
        if (strpos($itemsString, ',') && strpos($itemsString, ';')) {
            $return = [];
            $items = explode(',', $itemsString);
            foreach ($items as $itemPair) {
                $item = strpos($itemPair, ';') !== false ? explode(';', $itemPair) : [$itemPair, $itemPair];
                $return[$item[0]] = $item[1];
            }
            return $return;
        } elseif (strpos($itemsString, ',')) {
            $items = explode(',', $itemsString);
            return array_combine($items, $items);
        }
        return [$itemsString => $itemsString];
    }

    public function getName(): string
    {
        if (null !== $this->getParent()) {
            return $this->getParent()->getName() . '_' . $this->name;
        }
        return $this->name ?? 'select';
    }

    /**
     * @return array
     */
    public function getFormattedItems()
    {
        $items = $this->getItems();
        if (true === $items instanceof \Traversable) {
            $items = iterator_to_array($items);
        }
        if (true === is_array($items)) {
            return $items;
        }
        return $this->buildItems((string) $items);
    }

    /**
     * @param \Traversable|string|null $items
     * @return Select
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return \Traversable|string|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $mode
     * @return Select
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }
}
