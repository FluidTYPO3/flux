<?php
namespace FluidTYPO3\Flux\UserFunction;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
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
 ***************************************************************/

/**
 * Renders an exception error message in a nice way
 *
 * @package	Flux
 * @subpackage UserFunction
 */
class ErrorReporter {

	/**
	 * @param array $parameters
	 * @param object $pObj Not used
	 * @return string
	 */
	public function renderField(&$parameters, &$pObj) {
		unset($pObj);
		$exception = $parameters['fieldConf']['config']['parameters'][0]['exception'];
		if ($exception instanceof \Exception) {
			$code = $exception->getCode();
			$message = $exception->getMessage();
			$type = get_class($exception);
			return 'An ' . $type . ' was encountered while rendering the FlexForm.<br /><br />
				The error code is ' . $code . ' and the message states: ' . $message;
		} else {
			return 'An error was encountered while rendering the FlexForm.<br /><br />
					The error message states: ' . $exception;
		}
	}
}
