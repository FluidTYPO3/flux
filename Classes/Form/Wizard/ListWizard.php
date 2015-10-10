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
 * ListWizard
 */
class ListWizard extends AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'list';

	/**
	 * @var string
	 */
	protected $type = 'popup';

	/**
	 * @var string
	 */
	protected $icon = 'list.gif';

	/**
	 * @var array
	 */
	protected $module = array(
		'name' => 'wizard_list'
	);

	/**
	 * @var string
	 */
	protected $table;

	/**
	 * @var integer
	 */
	protected $height = 500;

	/**
	 * @var integer
	 */
	protected $width = 400;

	/**
	 * @var integer
	 */
	protected $storagePageUid;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$structure = array(
			'JSopenParams' => 'height=' . $this->getHeight() . ',width=' . $this->getWidth() . ',status=0,menubar=0,scrollbars=1',
			'params' => array(
				'table' => $this->getTable(),
				'pid' => $this->getStoragePageUid(),
			)
		);
		return $structure;
	}

	/**
	 * @param integer $height
	 * @return ListWizard
	 */
	public function setHeight($height) {
		$this->height = $height;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getHeight() {
		return $this->height;
	}

	/**
	 * @param integer $width
	 * @return ListWizard
	 */
	public function setWidth($width) {
		$this->width = $width;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getWidth() {
		return $this->width;
	}

	/**
	 * @param integer $storagePageUid
	 * @return ListWizard
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
	 * @return ListWizard
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
