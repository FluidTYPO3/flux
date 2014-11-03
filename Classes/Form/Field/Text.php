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

use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
			$typoScript = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
			$configuration['defaultExtras'] = $typoScript['plugin.']['tx_flux.']['settings.']['flexform.']['rteDefaults'];
		} else {
			$configuration['defaultExtras'] = $defaultExtras;
		}
		return $configuration;
	}

	/**
	 * @param integer $columns
	 * @return \FluidTYPO3\Flux\Form\Field\Text
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
