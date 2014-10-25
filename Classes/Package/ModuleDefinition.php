<?php
namespace FluidTYPO3\Flux\Package;
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
 * Class ModuleDefinition
 *
 * Defines one backend module which can be used by TYPO3.
 */
class ModuleDefinition extends PluginDefinition implements CollectableInterface {

	const DEFAULT_GROUP = 'web';

	/**
	 * @var string
	 */
	protected $group;

	/**
	 * @param string $name
	 * @param string $label
	 * @param string $icon
	 * @param array $actions
	 * @param array $uncachedActions
	 * @param string $group
	 */
	public function __construct($name, $label, $icon, $actions = NULL, $uncachedActions = NULL, $group = self::DEFAULT_GROUP) {
		parent::__construct($name, $label, $icon, $actions, $uncachedActions);
		$this->group = $group;
	}

	/**
	 * @return string
	 */
	public function getGroup() {
		return $this->group;
	}

}
