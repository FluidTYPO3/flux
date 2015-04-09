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
	 * @var array
	 */
	protected $module = array(
		'name' => 'wizard_add'
	);

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
			'params' => array(
				'table'=> $this->getTable(),
				'pid' => $this->getStoragePageUid(),
				'setValue' => intval($this->getSetValue())
			)
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
