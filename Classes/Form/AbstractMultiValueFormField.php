<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractMultiValueFormField
 */
abstract class AbstractMultiValueFormField extends AbstractFormField implements MultiValueFieldInterface
{

    /**
     * @var integer
     */
    protected $size = 1;

    /**
     * @var boolean
     */
    protected $multiple = false;

    /**
     * @var integer
     */
    protected $minItems = 0;

    /**
     * @var integer
     */
    protected $maxItems;

    /**
     * @var string
     */
    protected $itemListStyle;

    /**
     * @var string
     */
    protected $selectedListStyle;

    /**
     * Special rendering type of this component - supports all values normally
     * supported by TCA of the "select" field type.
     *
     * @var string
     * @see https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Select/Index.html#rendertype
     */
    protected $renderType;

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
     * @var string
     * @see https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Type/Select.html#itemsprocfunc
     */
    protected $itemsProcFunc;

    /**
     * If not-FALSE, adds one empty option/value pair to the generated selector
     * box and tries to use this property's value (cast to string) as label.
     *
     * @var boolean|string
     */
    protected $emptyOption = false;

    /**
     * If set to TRUE, Flux will attempt to translate the LLL labels of items
     * provided as CSV values, e.g. items "foo,bar" would try to resolve LLL
     * values for "LLL:EXT:myext/Resources/Private/Languages/locallang.xlf:foo"
     * and "LLL:EXT:myext/Resources/Private/Languages/locallang.xlf:bar" to be
     * used as value labels.
     *
     * @var boolean
     */
    protected $translateCsvItems = false;

    /**
     * @param string $type
     * @return array
     */
    public function prepareConfiguration($type)
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

    /**
     * @param integer $size
     * @return MultiValueFieldInterface
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param boolean $multiple
     * @return MultiValueFieldInterface
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param integer $maxItems
     * @return MultiValueFieldInterface
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxItems()
    {
        return $this->maxItems;
    }

    /**
     * @param integer $minItems
     * @return MultiValueFieldInterface
     */
    public function setMinItems($minItems)
    {
        $this->minItems = $minItems;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMinItems()
    {
        return $this->minItems;
    }

    /**
     * @param string $itemListStyle
     * @return MultiValueFieldInterface
     */
    public function setItemListStyle($itemListStyle)
    {
        $this->itemListStyle = $itemListStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getItemListStyle()
    {
        return $this->itemListStyle;
    }

    /**
     * @param string $selectedListStyle
     * @return MultiValueFieldInterface
     */
    public function setSelectedListStyle($selectedListStyle)
    {
        $this->selectedListStyle = $selectedListStyle;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelectedListStyle()
    {
        return $this->selectedListStyle;
    }

    /**
     * @return string
     */
    public function getRenderType()
    {
        return $this->renderType;
    }

    /**
     * @param string $renderType
     * @return MultiValueFieldInterface
     */
    public function setRenderType($renderType)
    {
        $this->renderType = $renderType;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTranslateCsvItems()
    {
        return $this->translateCsvItems;
    }

    /**
     * @param boolean $translateCsvItems
     * @return MultiValueFieldInterface
     */
    public function setTranslateCsvItems($translateCsvItems)
    {
        $this->translateCsvItems = $translateCsvItems;
        return $this;
    }

    /**
     * @param array $items
     * @return MultiValueFieldInterface
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = [];
        if (true === $this->items instanceof QueryInterface) {
            $items = $this->addOptionsFromResults($this->items);
        } elseif (true === is_string($this->items)) {
            if (false !== strpos($this->items, '..')) {
                list ($low, $high) = explode('..', $this->items);
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
            array_unshift($items, [$emptyOption, '']);
        }
        return $items;
    }

    /**
     * @param string $itemsProcFunc
     * @return MultiValueFieldInterface
     */
    public function setItemsProcFunc($itemsProcFunc)
    {
        $this->itemsProcFunc = $itemsProcFunc;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getItemsProcFunc()
    {
        return $this->itemsProcFunc;
    }

    /**
     * @param boolean|string $emptyOption
     * @return MultiValueFieldInterface
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    /**
     * @return boolean|string
     */
    public function getEmptyOption()
    {
        return $this->emptyOption;
    }

    /**
     * @param string $table
     * @param string $type
     * @return string
     */
    protected function getLabelPropertyName($table, $type)
    {
        $typoScript = $this->getConfigurationService()->getAllTypoScript();
        if (true === isset($typoScript['config']['tx_extbase']['persistence']['classes'][$type])) {
            $mapping = $typoScript['config']['tx_extbase']['persistence']['classes'][$type];
            if (true === isset($mapping['mapping']['tableName'])) {
                $table = $mapping['mapping']['tableName'];
            }
        }
        $labelField = $GLOBALS['TCA'][$table]['ctrl']['label'];
        $propertyName = GeneralUtility::underscoredToLowerCamelCase($labelField);
        return $propertyName;
    }

    /**
     * @param QueryInterface $query
     * @return array
     */
    protected function addOptionsFromResults(QueryInterface $query)
    {
        $items = [];
        $results = $query->execute();
        $type = $query->getType();
        $table = strtolower(str_replace('\\', '_', $type));
        $propertyName = $this->getLabelPropertyName($table, $type);
        foreach ($results as $result) {
            $uid = $result->getUid();
            array_push($items, [ObjectAccess::getProperty($result, $propertyName), $uid]);
        }
        return $items;
    }

}
