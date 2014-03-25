<?php
namespace FluidTYPO3\Flux\UserFunction;
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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 */
class ErrorReporterTest extends AbstractUserFunctionTest {

	const FAKE_MESSAGE = 'This is a demo Exception';
	const FAKE_CODE = 1374506190;

	/**
	 * @var array
	 */
	protected $parameters = array(
		'fieldConf' => array(
			'config' => array(
				'parameters' => array(
				)
			)
		)
	);

	/**
	 * @return array
	 */
	protected function getParameters() {
		$parameters = $this->parameters;
		$parameters['fieldConf']['config']['parameters'] = array(
			array(
				'exception' => 'Ignored text ' . self::FAKE_MESSAGE . ' (' . self::FAKE_CODE . ') ignored text'
			)
		);
		return $parameters;
	}

	/**
	 * @test
	 */
	public function supportsExceptionAsParameter() {
		$userFunctionReference = $this->getClassName() . '->' . $this->methodName;
		$parameters = $this->getParameters();
		$parameters['fieldConf']['config']['parameters'][0]['exception'] = new \Exception(self::FAKE_MESSAGE, self::FAKE_CODE);
		$output = GeneralUtility::callUserFunction($userFunctionReference, $parameters, $this->getCallerInstance());
		$this->assertOutputContainsExpectedMessageAndCode($output);
	}

	/**
	 * @test
	 */
	public function renderedErrorReportContainsExceptionMessageAndCode() {
		$output = $this->canCallMethodAndReceiveOutput();
		$this->assertOutputContainsExpectedMessageAndCode($output);
	}

	/**
	 * @param string $output
	 */
	protected function assertOutputContainsExpectedMessageAndCode($output) {
		$this->assertContains(self::FAKE_MESSAGE, $output);
		$this->assertContains(strval(self::FAKE_CODE), $output);
	}

}
