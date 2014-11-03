<?php
namespace FluidTYPO3\Flux\Utility;
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

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @package Flux
 */
class MiscellaneousUtiltyTest extends AbstractTestCase {

	/**
	 * Setup
	 */
	protected function setUp() {
		parent::setUp();
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = array();
	}

	/**
	 * @return array
	 */
	protected function getClipBoardDataFixture() {
		$clipBoardData = array(
			'current' => 'normal',
			'normal' => array(
				'el' => \FluidTYPO3\Flux\Tests\Fixtures\Data\Records::$contentRecordWithoutParentAndWithoutChildren
			)
		);
		return $clipBoardData;
	}

	/**
	 * @return array
	 */
	protected function getFormOptionsFixture() {
		$formOptionsData = array(
			'extensionName' => 'mockextension',
			'iconOption' => 'Icons/Mock/Fixture.gif',
		);
		return $formOptionsData;
	}

	/**
	 * @return Form
	 */
	protected function getFormInstance() {
		/** @var ObjectManagerInterface $objectManager */
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		/** @var Form $instance */
		$instance = $objectManager->get('FluidTYPO3\Flux\Form');
		return $instance;
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrl() {
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3');
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReference() {
		$clipBoardData = $this->getClipBoardDataFixture();
		$clipBoardData['normal']['mode'] = 'reference';
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReferenceReturnsEmptyStringIfModeIsCut() {
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertIsString($iconWithUrl);
		$this->assertEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @test
	 */
	public function canGetIconForTemplateIfIconOptionIsSet() {
		$formOptionsFixture = $this->getFormOptionsFixture();
		/** @var Form $form */
		$form = $this->getFormInstance();
		$form->setOption($form::OPTION_ICON, $formOptionsFixture['iconOption']);
		$icon = MiscellaneousUtility::getIconForTemplate($form);
		$this->assertEquals($formOptionsFixture['iconOption'], $icon);
	}

	/**
	 * @test
	 */
	public function returnFalseResultForGivenTemplateButNoTemplateIconIsFound() {
		$formOptionsFixture = $this->getFormOptionsFixture();
		$mockExtensionUrl = $this->getMockExtension();
		/** @var Form $form */
		$form = $this->getFormInstance();
		$form->setOption($form::OPTION_TEMPLATEFILE, $mockExtensionUrl . '/' . $formOptionsFixture['extensionName'] . '/Resources/Private/Templates/Content/TestFalse.html');
		$form->setExtensionName($formOptionsFixture['extensionName']);
		$icon = MiscellaneousUtility::getIconForTemplate($form);
		$this->assertFalse($icon);
	}

	/**
	 * @test
	 */
	public function returnFalseResultIfNoTemplateAndNoIconOptionIsSet() {
		$form = $this->getFormInstance();
		$icon = MiscellaneousUtility::getIconForTemplate($form);
		$this->assertFalse($icon);
	}

	/**
	 * @return string
	 */
	protected function getMockExtension() {
		$structure = array(
			'mockextension' => array(
				'Resources' => array(
					'Private' => array(
						'Templates' => array(
							'Content' => array(
								'TestTrue.html' => 'Test template with Icon available',
								'TestFalse.html' => 'Test template with Icon not available'
							)
						)
					),
					'Public' => array(
						'Icons' => array(
							'Content' => array(
								'TestTrue.png' => 'Test-Icon'
							)
						)
					)
				),
			)
		);
		vfsStream::setup('ext', NULL, $structure);
		$vfsUrl = vfsStream::url('ext');

		return $vfsUrl;
	}
}
