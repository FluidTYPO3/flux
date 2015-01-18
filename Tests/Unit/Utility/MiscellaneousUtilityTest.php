<?php
namespace FluidTYPO3\Flux\Tests\Unit\Utility;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use FluidTYPO3\Flux\Utility\MiscellaneousUtility;
use FluidTYPO3\Flux\Utility\ClipBoardUtility;
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
				'el' => Records::$contentRecordWithoutParentAndWithoutChildren
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
	 * @disabledtest
	 */
	public function canCreateIconWithUrl() {
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3');
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
	}

	/**
	 * @disabledtest
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
	 * @disabledtest
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
