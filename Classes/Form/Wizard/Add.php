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
class Add extends AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'add';

	/**
	 * @var string
	 */
	protected $type = 'script';

	/**
	 * @var string
	 */
	protected $icon = 'add.gif';

	/**
	 * @var string
	 */
	protected $script = 'wizard_add.php';

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var integer
	 */
	protected $storagePageUid;

	/**
	 * @var boolean
	 */
	protected $setValue = TRUE;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = array(
			'table' => $this->getTable(),
			'pid' => $this->getStoragePageUid(),
			'setValue' => intval($this->getSetValue())
		);
		return $configuration;
	}

	/**
	 * @param boolean $setValue
	 * @return Add
	 */
	public function setSetValue($setValue) {
		$this->setValue = $setValue;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSetValue() {
		return $this->setValue;
	}

	/**
	 * @param integer $storagePageUid
	 * @return Add
	 */
	public function setStoragePageUid($storagePageUid) {
		$this->storagePageUid = $storagePageUid;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getStoragePageUid() {
		return $this->storagePageUid;
	}

	/**
	 * @param string $table
	 * @return Add
	 */
	public function setTable($table) {
		$this->table = $table;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

}
