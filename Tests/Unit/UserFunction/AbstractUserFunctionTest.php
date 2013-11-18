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
abstract class Tx_Flux_UserFunction_AbstractUserFunctionTest extends Tx_Flux_Tests_AbstractFunctionalTest {

	/**
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * @var string
	 */
	protected $methodName = 'renderField';

	/**
	 * @var boolean
	 */
	protected $expectsNull = FALSE;

	/**
	 * @return array
	 */
	protected function getParameters() {
		return $this->parameters;
	}

	/**
	 * @return string
	 */
	protected function getClassName() {
		$className = 'Tx_Flux_UserFunction_' . substr(array_pop(explode('_', get_class($this))), 0, -4);
		return $className;
	}

	/**
	 * @return object
	 */
	protected function createInstance() {
		$className = $this->getClassName();
		$instance = $this->objectManager->get($className);
		return $instance;
	}

	/**
	 * @return object
	 */
	protected function getCallerInstance() {
		return $this;
	}

	/**
	 * @test
	 */
	public function canCreateInstance() {
		$instance = $this->createInstance();
		$this->assertInstanceOf($this->getClassName(), $instance);
	}

	/**
	 * @test
	 */
	public function canCallMethodAndReceiveOutput() {
		$userFunctionReference = $this->getClassName() . '->' . $this->methodName;
		$output = \TYPO3\CMS\Core\Utility\GeneralUtility::callUserFunction($userFunctionReference, $this->getParameters(), $this->getCallerInstance());
		if (TRUE === $this->expectsNull) {
			$this->assertNull($output);
		} else {
			$this->assertNotEmpty($output);
		}
		return $output;
	}

}
