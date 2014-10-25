<?php
namespace FluidTYPO3\Flux\Collection;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

/**
 * Class Collection
 */
class Collection {

	/**
	 * @var CollectableInterface[]
	 */
	protected $members = array();

	/**
	 * @param Collection $other
	 * @return void
	 */
	public function merge(Collection $other) {
		foreach ($other->getAll() as $collectable) {
			$this->add($collectable);
		}
	}

	/**
	 * @param CollectableInterface $member
	 * @return void
	 */
	public function add(CollectableInterface $member) {
		$name = $member->getName();
		$name = TRUE === empty($name) ? uniqid('member') : $name;
		$this->members[$name] = $member;
	}

	/**
	 * @param string $name
	 * @return CollectableInterface|NULL
	 */
	public function get($name) {
		return TRUE === isset($this->members[$name]) ? $this->members[$name] : NULL;
	}

	/**
	 * @return CollectableInterface[]
	 */
	public function getAll() {
		return $this->members;
	}

	/**
	 * @param CollectableInterface|string $member
	 * @return void
	 */
	public function remove($member) {
		$name = TRUE === $member instanceof CollectableInterface ? $member->getName() : $member;
		if (TRUE === isset($this->members[$name])) {
			unset($this->members[$name]);
		}
	}

}
