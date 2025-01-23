<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Service\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

abstract class AbstractMultiValueFormField extends AbstractFormField implements MultiValueFieldInterface
{
    protected int $size = 1;
    protected bool $multiple = false;
    protected int $minItems = 0;
    protected ?int $maxItems = null;
    protected ?string $itemListStyle = '';
    protected ?string $selectedListStyle = '';

    /**
     * Special rendering type of this component - supports all values normally
     * supported by TCA of the "select" field type.
     *
     * @see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Select/Index.html#rendertype
     */
    protected ?string $renderType = 'selectSingle';

    /**
     * Mixed - string (CSV), Traversable or array of items. Format of key/value
     * pairs is also optional. For single-dim arrays, key becomes option value
     * and each member value becomes label. For multidim/Traversable each member
     * is inspected; if it is a raw value it is used for both value and label,
     * if it is a scalar value the first item is used as value and the second
     * as label.
     *
     * @var mixed
     */
    protected $items = null;

    /**
     * @see https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Type/Select.html#itemsprocfunc
     */
    protected ?string $itemsProcFunc = null;

    /**
     * If not-FALSE, adds one empty option/value pair to the generated selector
     * box and tries to use this property's value (cast to string) as label.
     * Can also be an array of [$value, $label, $iconName] where label and icon
     * name are optional - use this when you need to specify an icon for "empty".
     *
     * @var boolean|string|array
     */
    protected $emptyOption = false;

    /**
     * If set to TRUE, Flux will attempt to translate the LLL labels of items
     * provided as CSV values, e.g. items "foo,bar" would try to resolve LLL
     * values for "LLL:EXT:myext/Resources/Private/Languages/locallang.xlf:foo"
     * and "LLL:EXT:myext/Resources/Private/Languages/locallang.xlf:bar" to be
     * used as value labels.
     */
    protected bool $translateCsvItems = false;

    public function prepareConfiguration(string $type): array
    {
        $configuration = parent::prepareConfiguration($type);
        $configuration['size'] = $this->getSize();
        $configuration['maxitems'] = $this->getMaxItems();
        $configuration['minitems'] = $this->getMinItems();
        $configuration['multiple'] = $this->getMultiple();
        $configuration['itemListStyle'] = $this->getItemListStyle();
        $configuration['selectedListStyle'] = $this->getSelectedListStyle();
        $configuration['renderType'] = $this->getRenderType();
        $configuration['items'] = $this->getItems();
        $configuration['itemsProcFunc'] = $this->getItemsProcFunc();
        return $configuration;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMaxItems(int $maxItems): self
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function setMinItems(int $minItems): self
    {
        $this->minItems = $minItems;
        return $this;
    }

    public function getMinItems(): int
    {
        return $this->minItems;
    }

    public function setItemListStyle(?string $itemListStyle): self
    {
        $this->itemListStyle = $itemListStyle;
        return $this;
    }

    public function getItemListStyle(): ?string
    {
        return $this->itemListStyle;
    }

    public function setSelectedListStyle(?string $selectedListStyle): self
    {
        $this->selectedListStyle = $selectedListStyle;
        return $this;
    }

    public function getSelectedListStyle(): ?string
    {
        return $this->selectedListStyle;
    }

    public function getRenderType(): ?string
    {
        return $this->renderType;
    }

    public function setRenderType(?string $renderType): self
    {
        $this->renderType = $renderType;
        return $this;
    }

    public function getTranslateCsvItems(): bool
    {
        return $this->translateCsvItems;
    }

    public function setTranslateCsvItems(bool $translateCsvItems): self
    {
        $this->translateCsvItems = $translateCsvItems;
        return $this;
    }

    /**
     * @param array|string $items
     */
    public function setItems($items): self
    {
        $this->items = $items;
        return $this;
    }

    public function getItems(): array
    {
        $items = [];
        if (true === $this->items instanceof QueryInterface) {
            $items = $this->addOptionsFromResults($this->items);
        } elseif (true === is_string($this->items)) {
            if (false !== strpos($this->items, '..')) {
                [$low, $high] = explode('..', $this->items);
                $itemNames = range($low, $high, 1);
            } else {
                $itemNames = GeneralUtility::trimExplode(',', $this->items);
            }
            if (!$this->getTranslateCsvItems()) {
                foreach ($itemNames as $itemName) {
                    array_push($items, [$itemName, $itemName]);
                }
            } else {
                foreach ($itemNames as $itemName) {
                    $resolvedLabel = $this->resolveLocalLanguageValueOfLabel(
                        '',
                        $this->getPath() . '.option.' . $itemName
                    );
                    array_push($items, [$resolvedLabel, $itemName]);
                }
            }
        } elseif (true === is_array($this->items) || true === $this->items instanceof \Traversable) {
            foreach ($this->items as $itemIndex => $itemValue) {
                if (true === is_array($itemValue) || true === $itemValue instanceof \ArrayObject) {
                    array_push($items, $itemValue);
                } else {
                    array_push($items, [$itemValue, $itemIndex]);
                }
            }
        }
        $emptyOption = $this->getEmptyOption();
        if (false !== $emptyOption) {
            if (is_array($emptyOption)) {
                array_unshift($items, $emptyOption);
            } else {
                array_unshift($items, [$emptyOption, '']);
            }
        }
        return $items;
    }

    public function setItemsProcFunc(?string $itemsProcFunc): self
    {
        $this->itemsProcFunc = $itemsProcFunc;
        return $this;
    }

    public function getItemsProcFunc(): ?string
    {
        return $this->itemsProcFunc;
    }

    /**
     * @param boolean|string|array $emptyOption
     */
    public function setEmptyOption($emptyOption): self
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    /**
     * @return boolean|string|array
     */
    public function getEmptyOption()
    {
        return $this->emptyOption;
    }

    protected function getLabelPropertyName(string $table, string $type): string
    {
        $path = sprintf('config.tx_extbase.persistence.classes.%s.mapping.tableName', $type);
        $mappedTable = $this->getTypoScriptService()->getTypoScriptByPath($path);
        $labelField = $GLOBALS['TCA'][$mappedTable ?: $table]['ctrl']['label'];
        $propertyName = GeneralUtility::underscoredToLowerCamelCase($labelField);
        return $propertyName;
    }

    protected function addOptionsFromResults(QueryInterface $query): array
    {
        $items = [];
        $results = $query->execute();
        $type = $query->getType();
        $table = strtolower(str_replace('\\', '_', $type));
        $propertyName = $this->getLabelPropertyName($table, $type);
        /** @var DomainObjectInterface $result */
        foreach ($results as $result) {
            $uid = $result->getUid();
            array_push($items, [ObjectAccess::getProperty($result, $propertyName), $uid]);
        }
        return $items;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getTypoScriptService(): TypoScriptService
    {
        /** @var TypoScriptService $typoScriptService */
        $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
        return $typoScriptService;
    }
}
