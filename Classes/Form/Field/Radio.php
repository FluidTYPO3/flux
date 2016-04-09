<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class Radio extends AbstractMultiValueFormField {

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
	protected $items = NULL;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = parent::prepareConfiguration('radio');
		$configuration['items'] = $this->getItems();
		return $configuration;
	}

	/**
	 * @param array $items
	 * @return Select
	 */
	public function setItems($items) {
		$this->items = $items;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getItems() {
		$items = array();
		if (TRUE === $this->items instanceof QueryInterface) {
			$items = $this->addOptionsFromResults($this->items);
		} elseif (TRUE === is_string($this->items)) {
			$itemNames = GeneralUtility::trimExplode(',', $this->items);
			foreach ($itemNames as $itemName) {
				array_push($items, array($itemName, $itemName));
			}
		} elseif (TRUE === is_array($this->items) || TRUE === $this->items instanceof \Traversable) {
			foreach ($this->items as $itemIndex => $itemValue) {
				if (TRUE === is_array($itemValue) || TRUE === $itemValue instanceof \ArrayObject) {
					array_push($items, $itemValue);
				} else {
					array_push($items, array($itemValue, $itemIndex));
				}
			}
		}
		return $items;
	}

	/**
	 * @param QueryInterface $query
	 * @return array
	 */
	protected function addOptionsFromResults(QueryInterface $query) {
		$items = array();
		$results = $query->execute();
		$type = $query->getType();
		$table = strtolower(str_replace('\\', '_', $type));
		$propertyName = $this->getLabelPropertyName($table, $type);
		foreach ($results as $result) {
			$uid = $result->getUid();
			array_push($items, array(ObjectAccess::getProperty($result, $propertyName), $uid));
		}
		return $items;
	}

	/**
	 * @param string $table
	 * @param string $type
	 * @return string
	 */
	protected function getLabelPropertyName($table, $type) {
		$typoScript = $this->getConfigurationService()->getAllTypoScript();
		if (TRUE === isset($typoScript['config']['tx_extbase']['persistence']['classes'][$type])) {
			$mapping = $typoScript['config']['tx_extbase']['persistence']['classes'][$type];
			if (TRUE === isset($mapping['mapping']['tableName'])) {
				$table = $mapping['mapping']['tableName'];
			}
		}
		$labelField = $GLOBALS['TCA'][$table]['ctrl']['label'];
		$propertyName = GeneralUtility::underscoredToLowerCamelCase($labelField);
		return $propertyName;
	}

}
