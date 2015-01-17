<?php
namespace FluidTYPO3\Flux\Controller;
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

use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\Controller\AbstractFluxControllerTestCase;
use FluidTYPO3\Flux\Utility\ResolveUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @package Flux
 */
class ContentControllerTest extends AbstractFluxControllerTestCase {

	/**
	 * @test
	 */
	public function canRegisterCustomControllerForContent() {
		$this->performDummyRegistration();
	}

	/**
	 * @test
	 */
	public function canDetectPresenceOfRegisteredCustomControllerForContent() {
		$this->performDummyRegistration();
		$hasController = ResolveUtility::resolveFluxControllerClassNameByExtensionKeyAndAction('FluidTYPO3.Flux', 'render', 'Content');
		$this->assertTrue(class_exists($hasController));
	}

	/**
	 * @return AbstractFluxController
	 */
	protected function createAndTestDummyControllerInstance() {
		$record = Records::$contentRecordWithoutParentAndWithoutChildren;
		$record['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		$record['tx_fed_fcefile'] = 'Flux:Default.html';
		$frontend = new TypoScriptFrontendController($GLOBALS['TYPO3_CONF_VARS'], 1, 0);
		$frontend->cObj = new ContentObjectRenderer();
		$frontend->cObj->start($record);
		$this->performDummyRegistration();
		$controllerClassName = substr(get_class($this), 0, -4);
		/** @var AbstractFluxController $instance */
		$instance = $this->objectManager->get($controllerClassName);
		ObjectAccess::setProperty($instance, 'extensionName', 'Flux', TRUE);
		ObjectAccess::getProperty($instance, 'configurationManager', TRUE)->setContentObject($frontend->cObj);
		return $instance;
	}

}
