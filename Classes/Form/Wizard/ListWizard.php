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
	 * @var string
	 */
	protected $script = 'wizard_list.php';

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
			'JSopenParams' => 'height=' . $this->arguments['height'] . ',width=' . $this->arguments['width'] . ',status=0,menubar=0,scrollbars=1',
			'params' => array(
				'table' => $this->arguments['table'],
				'pid' => $this->arguments['pid'],
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