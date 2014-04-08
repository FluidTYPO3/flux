<?php
namespace FluidTYPO3\Flux\Utility;
use FluidTYPO3\Flux\Provider\ProviderInterface;
use FluidTYPO3\Flux\View\ExposedTemplateView;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 ***************************************************************/

class DebuggerUtility extends \TYPO3\CMS\Extbase\Utility\DebuggerUtility {

	/**
	 * @var array
	 */
	protected static $sentDebugMessages = array();

	/**
	 * @var array
	 */
	protected static $friendlySeverities = array(
		GeneralUtility::SYSLOG_SEVERITY_INFO,
		GeneralUtility::SYSLOG_SEVERITY_NOTICE
	);

	/**
	 * @param mixed $instance
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public static function debug($instance, $plainText = FALSE, $depth = 2) {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			if (TRUE === $instance instanceof \Exception) {
				GeneralUtility::sysLog('Flux Debug: Suppressed Exception - "' . $instance->getMessage() . '" (' . $instance->getCode() . ')', 'flux');
			}
			return;
		}
		if (TRUE === is_object($instance)) {
			$hash = spl_object_hash($instance);
		} else {
			$hash = microtime(TRUE);
		}
		if (TRUE === isset(self::$sentDebugMessages[$hash])) {
			return;
		}
		if (TRUE === $instance instanceof ExposedTemplateView) {
			self::debugView($instance, $plainText, $depth);
		} elseif (TRUE === $instance instanceof ProviderInterface) {
			self::debugProvider($instance, $plainText, $depth);
		} elseif (TRUE === $instance instanceof \Exception) {
			self::debugException($instance, $plainText, $depth);
		} else {
			self::debugMixed($instance, $plainText, $depth);
		}
		self::$sentDebugMessages[$hash] = TRUE;
	}

	/**
	 * @param mixed $variable
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public static function debugMixed($variable, $plainText = FALSE, $depth = 2) {
		self::passToDebugger($variable, 'Flux variable debug', $depth, $plainText, FALSE);
	}

	/**
	 * @param \Exception $error
	 * @return void
	 */
	public static function debugException(\Exception $error) {
		self::message($error->getMessage() . ' (' . $error->getCode() . ')', GeneralUtility::SYSLOG_SEVERITY_FATAL);
	}

	/**
	 * @param ExposedTemplateView $view
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public static function debugView(ExposedTemplateView $view, $plainText = FALSE, $depth = 2) {
		self::passToDebugger($view, 'Flux View debug', $depth, $plainText, FALSE);;
	}

	/**
	 * @param ProviderInterface $provider
	 * @param boolean $plainText
	 * @param integer $depth
	 * @return void
	 */
	public static function debugProvider(ProviderInterface $provider, $plainText = FALSE, $depth = 2) {
		self::passToDebugger($provider, 'Flux Provider debug', $depth, $plainText, FALSE);
	}

	/**
	 * @return void
	 */
	protected static function passToDebugger() {
		call_user_func_array(array(self, 'var_dump'), array(func_get_args()));
	}

	/**
	 * @param string $message
	 * @param integer $severity
	 * @param string $title
	 * @return NULL
	 */
	public static function message($message, $severity = GeneralUtility::SYSLOG_SEVERITY_INFO, $title = 'Flux Debug') {
		if (1 > $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode']) {
			return NULL;
		}
		$hash = $message . $severity;
		if (TRUE === isset(self::$sentDebugMessages[$hash])) {
			return NULL;
		}
		if (2 == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flux']['setup']['debugMode'] && TRUE === in_array($severity, self::$friendlySeverities)) {
			return NULL;
		}
		$isAjaxCall = (boolean) 0 < GeneralUtility::_GET('ajaxCall');
		$flashMessage = new FlashMessage($message, $title, $severity);
		$flashMessage->setStoreInSession($isAjaxCall);
		FlashMessageQueue::addMessage($flashMessage);
		self::$sentDebugMessages[$hash] = TRUE;
		return NULL;
	}

}
