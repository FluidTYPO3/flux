<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>
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
 * ************************************************************* */

/**
 * @author Claus Due <claus@wildside.dk>
 * @package Flux
 */
class Tx_Flux_Form_Wizard_LinkTest extends Tx_Flux_Tests_Functional_Form_Field_AbstractWizardTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'name' => 'test',
		'label' => 'Test field',
		'hideParent' => FALSE,
		'blindLinkOptions' => array('new', 'info'),
		'allowedExtensions' => array('pdf', 'txt'),
		'height' => 400,
		'width' => 300
	);

	/**
	 * @test
	 */
	public function canUseStringAsBlindLinkOptionsList() {
		$extensions = implode(',', $this->chainProperties['blindLinkOptions']);
		$instance = $this->createInstance();
		$fetched = $instance->setBlindLinkOptions($extensions)->getBlindLinkOptions();
		$this->assertSame($this->chainProperties['blindLinkOptions'], $fetched);
	}

	/**
	 * @test
	 */
	public function canUseStringAsAllowedExtensionList() {
		$extensions = implode(',', $this->chainProperties['allowedExtensions']);
		$instance = $this->createInstance();
		$fetched = $instance->setAllowedExtensions($extensions)->getAllowedExtensions();
		$this->assertSame($this->chainProperties['allowedExtensions'], $fetched);
	}

}
