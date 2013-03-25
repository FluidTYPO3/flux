<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Claus Due <claus@wildside.dk>, Wildside A/S
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
 ***************************************************************/

/**
 * Debug Service
 *
 * Service to debug various aspects of Flux templates
 *
 * @package Flux
 * @subpackage Service
 */
class Tx_Flux_Service_Debug implements t3lib_Singleton {

	/**
	 * @param mixed $instance
	 * @return void
	 */
	public function debug($instance) {
		if (0 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			if (TRUE === $instance instanceof Exception) {
				t3lib_div::sysLog('Flux Debug: Suppressed Exception - "' . $instance->getMessage() . '" (' . $instance->getCode() . ')', 'flux');
			}
			return;
		}
		if (TRUE === $instance instanceof Tx_Flux_MVC_View_ExposedTemplateView) {
			$this->debugView($instance);
		} elseif (TRUE === $instance instanceof Tx_Flux_Provider_ConfigurationProviderInterface) {
			$this->debugProvider($instance);
		} elseif (TRUE === $instance instanceof Exception) {
			$this->debugException($instance);
		} else {
			$this->debugMixed($instance);
		}
	}

	/**
	 * @param mixed $variable
	 * @return void
	 */
	public function debugMixed($variable) {
		Tx_Extbase_Utility_Debugger::var_dump($variable);
	}

	/**
	 * @param Exception $error
	 * @return void
	 */
	public function debugException(Exception $error) {
		$this->message($error->getMessage() . ' (' . $error->getCode() . ')');
	}

	/**
	 * @param Tx_Flux_MVC_View_ExposedTemplateView $view
	 * @return void
	 */
	public function debugView(Tx_Flux_MVC_View_ExposedTemplateView $view) {
		Tx_Extbase_Utility_Debugger::var_dump($view);
	}

	/**
	 * @param Tx_Flux_Provider_ConfigurationProviderInterface $provider
	 * @return void
	 */
	public function debugProvider(Tx_Flux_Provider_ConfigurationProviderInterface $provider) {
		Tx_Extbase_Utility_Debugger::var_dump($provider);
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @return void
	 */
	public function message($message, $severity = t3lib_div::SYSLOG_SEVERITY_WARNING) {
		if (0 < $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			return;
		}
		$flashMessage = new t3lib_FlashMessage('Flux Debug:<br />' . $message, $severity);
		t3lib_FlashMessageQueue::addMessage($flashMessage);
	}

}
