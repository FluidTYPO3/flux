<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

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
