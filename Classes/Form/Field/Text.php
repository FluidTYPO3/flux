<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * @package Flux
 * @subpackage Form\Field
 */
class Text extends Input implements FieldInterface {

	/**
	 * @var integer
	 */
	protected $columns = 85;

	/**
	 * @var integer
	 */
	protected $rows = 10;

	/**
	 * @var string
	 */
	protected $defaultExtras;

	/**
	 * @var boolean
	 */
	protected $enableRichText = FALSE;

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$configuration = $this->prepareConfiguration('text');
		$configuration['rows'] = $this->getRows();
		$configuration['cols'] = $this->getColumns();
		$configuration['eval'] = $this->getValidate();
		$defaultExtras = $this->getDefaultExtras();
		if (TRUE === $this->getEnableRichText() && TRUE === empty($defaultExtras)) {
			$typoScript = $this->getConfigurationService()->getAllTypoScript();
			$configuration['defaultExtras'] = $typoScript['plugin']['tx_flux']['settings']['flexform']['rteDefaults'];
		} else {
			$configuration['defaultExtras'] = $defaultExtras;
		}
		return $configuration;
	}

	/**
	 * @param integer $columns
	 * @return Text
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * @param string $defaultExtras
	 * @return Text
	 */
	public function setDefaultExtras($defaultExtras) {
		$this->defaultExtras = $defaultExtras;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultExtras() {
		return $this->defaultExtras;
	}

	/**
	 * @param boolean $enableRichText
	 * @return Text
	 */
	public function setEnableRichText($enableRichText) {
		$this->enableRichText = (boolean) $enableRichText;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getEnableRichText() {
		return (boolean) $this->enableRichText;
	}

	/**
	 * @param integer $rows
	 * @return Text
	 */
	public function setRows($rows) {
		$this->rows = $rows;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getRows() {
		return $this->rows;
	}

}
