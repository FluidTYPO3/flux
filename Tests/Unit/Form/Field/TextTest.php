<?php
namespace FluidTYPO3\Flux\Form\Field;
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
 * ************************************************************* */

/**
 * @package Flux
 */
class TextTest extends InputTest {

	/**
	 * @var array
	 */
	protected $chainProperties = array(
		'name' => 'test',
		'label' => 'Test field',
		'enable' => TRUE,
		'maxCharacters' => 30,
		'maximum' => 10,
		'minimum' => 0,
		'validate' => 'trim,int',
		'default' => 'test',
		'columns' => 85,
		'rows' => 8,
		'requestUpdate' => TRUE,
	);

	/**
	 * @test
	 */
	public function canChainSetterForEnableRichText() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$chained = $instance->setEnableRichText(TRUE);
		$this->assertSame($instance, $chained);
		$this->assertTrue($instance->getEnableRichText());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canChainSetterForDefaultExtras() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$chained = $instance->setDefaultExtras('void');
		$this->assertSame($instance, $chained);
		$this->assertSame('void', $instance->getDefaultExtras());
		$this->performTestBuild($instance);
	}

	/**
	 * @test
	 */
	public function canBuildConfigurationWithEnableWithTextWithoutDefaultExtras() {
		/** @var Text $instance */
		$instance = $this->createInstance();
		$instance->setDefaultExtras(NULL)->setEnableRichText(TRUE);
		$this->performTestBuild($instance);
	}

}
