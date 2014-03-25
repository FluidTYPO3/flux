<?php
namespace FluidTYPO3\Flux\Form\Wizard;
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

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * @package Flux
 * @subpackage Form\Wizard
 */
class Select extends AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'select';

	/**
	 * @var string
	 */
	protected $type = 'select';

	/**
	 * @var string
	 */
	protected $icon = 'list.gif';

	/**
	 * @var string
	 */
	protected $script = 'wizard_list.php';

	/**
	 * @var string
	 */
	protected $mode = 'substitution';

	/**
	 * Comma-separated, comma-and-semicolon-separated or array
	 * list of possible values
	 *
	 * @var mixed
	 */
	protected $items;

	/**
	 * Build the configuration array
	 *
	 * @return array
	 */
	public function buildConfiguration() {
		return array(
			'mode' => $this->getMode(),
			'items' => $this->getFormattedItems()
		);
	}

	/**
	 * Builds an array of selector options based on a type of string
	 *
	 * @param string $itemsString
	 * @return array
	 */
	protected function buildItems($itemsString) {
		$itemsString = trim($itemsString, ',');
		if (strpos($itemsString, ',') && strpos($itemsString, ';')) {
			$return = array();
			$items = explode(',', $itemsString);
			foreach ($items as $itemPair) {
				$item = explode(';', $itemPair);
				$return[$item[0]] = $item[1];
			}
			return $return;
		} else if (strpos($itemsString, ',')) {
			$items = explode(',', $itemsString);
			return array_combine($items, $items);
		} else {
			return array($itemsString => $itemsString);
		}
	}

	/**
	 * @return string
	 */
	public function getName() {
		if (NULL !== $this->getParent()) {
			return $this->getParent()->getName() . '_' . $this->name;
		}
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getFormattedItems() {
		$items = $this->getItems();
		if (TRUE === $items instanceof \Traversable) {
			$items = iterator_to_array($items);
		}
		if (TRUE === is_array($items)) {
			return $items;
		}
		return $this->buildItems($items);
	}

	/**
	 * @param mixed $items
	 * @return Select
	 */
	public function setItems($items) {
		$this->items = $items;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @param string $mode
	 * @return Select
	 */
	public function setMode($mode) {
		$this->mode = $mode;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMode() {
		return $this->mode;
	}

}
