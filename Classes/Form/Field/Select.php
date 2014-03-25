<?php
namespace FluidTYPO3\Flux\Form\Field;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *****************************************************************/

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class Select extends AbstractMultiValueFormField {

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
	 * If not-FALSE, adds one empty option/value pair to the generated selector
	 * box and tries to use this property's value (cast to string) as label.
	 *
	 * @var boolean|string
	 */
	protected $emptyOption = FALSE;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = parent::prepareConfiguration('select');
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
		if (TRUE === is_string($this->items)) {
			$itemNames = GeneralUtility::trimExplode(',', $this->items);
			foreach ($itemNames as $itemName) {
				array_push($items, array($itemName, $itemName));
			}
		} elseif (TRUE === is_array($this->items) || TRUE === $this->items instanceof Traversable) {
			foreach ($this->items as $itemIndex => $itemValue) {
				if (TRUE === is_array($itemValue) || TRUE === $itemValue instanceof ArrayObject) {
					array_push($items, $itemValue);
				} else {
					array_push($items, array($itemValue, $itemIndex));
				}
			}
		} elseif (TRUE === $this->items instanceof Query) {
			/** @var Query $query */
			$query = $this->items;
			$results = $query->execute();
			$type = $query->getType();
			$typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$table = strtolower(str_replace('\\', '_', $type));
			if (TRUE === isset($typoScript['config.']['tx_extbase.']['persistence.']['classes.'][$type . '.'])) {
				$mapping = $typoScript['config.']['tx_extbase.']['persistence.']['classes.'][$type . '.'];
				if (TRUE === isset($mapping['mapping.']['tableName'])) {
					$table = $mapping['mapping.']['tableName'];
				}
			}
			$labelField = $GLOBALS['TCA'][$table]['ctrl']['label'];
			$propertyName = GeneralUtility::underscoredToLowerCamelCase($labelField);
			foreach ($results as $result) {
				$uid = $result->getUid();
				array_push($items, array(ObjectAccess::getProperty($result, $propertyName), $uid));
			}
		}
		$emptyOption = $this->getEmptyOption();
		if (FALSE !== $emptyOption) {
			array_unshift($items, array('', $emptyOption));
		}
		return $items;
	}

	/**
	 * @param boolean|string $emptyOption
	 * @return Select
	 */
	public function setEmptyOption($emptyOption) {
		$this->emptyOption = $emptyOption;
		return $this;
	}

	/**
	 * @return boolean|string
	 */
	public function getEmptyOption() {
		return $this->emptyOption;
	}

}
