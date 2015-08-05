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
		$GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable'] = [];
	}

	/**
	 * @return array
	 */
	protected function getClipBoardDataFixture() {
		$clipBoardData = [
			'current' => 'normal',
			'normal' => [
				'el' => Records::$contentRecordWithoutParentAndWithoutChildren
			]
		];
		return $clipBoardData;
	}

	/**
	 * @return array
	 */
	protected function getFormOptionsFixture() {
		$formOptionsData = [
			'extensionName' => 'flux',
			'iconOption' => 'Icons/Mock/Fixture.gif',
		];
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
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3');
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
		unset($GLOBALS['BE_USER']);
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReference() {
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$clipBoardData = $this->getClipBoardDataFixture();
		$clipBoardData['normal']['mode'] = 'reference';
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertNotEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
		unset($GLOBALS['BE_USER']);
	}

	/**
	 * @test
	 */
	public function canCreateIconWithUrlAsReferenceReturnsEmptyStringIfModeIsCut() {
		$GLOBALS['BE_USER'] = $this->getMock('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication');
		$clipBoardData = $this->getClipBoardDataFixture();
		ClipBoardUtility::setClipBoardData($clipBoardData);
		$iconWithUrl = ClipBoardUtility::createIconWithUrl('1-2-3', TRUE);
		$this->assertIsString($iconWithUrl);
		$this->assertEmpty($iconWithUrl);
		ClipBoardUtility::clearClipBoardData();
		unset($GLOBALS['BE_USER']);
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
		$this->assertNull($icon);
	}

	/**
	 * @test
	 */
	public function returnFalseResultIfNoTemplateAndNoIconOptionIsSet() {
		$form = $this->getFormInstance();
		$icon = MiscellaneousUtility::getIconForTemplate($form);
		$this->assertNull($icon);
	}

	/**
	 * @dataProvider getGenerateUniqueIntegerForFluxAreaTestValues
	 * @param integer $uid
	 * @param string $name
	 * @param integer $expected
	 */
	public function testGenerateUniqueIntegerForFluxArea($uid, $name, $expected) {
		$result = MiscellaneousUtility::generateUniqueIntegerForFluxArea($uid, $name);
		$this->assertEquals($expected, $result);
	}

	/**
	 * @return array
	 */
	public function getGenerateUniqueIntegerForFluxAreaTestValues() {
		return [
			[1, 'test', -10000000449],
			[321, 'foobar', -10000000954],
			[8, 'xyzbazbar', -10000000997],
			[123, 'verylongstringverylongstringverylongstring', -10000004770]
		];
	}

	/**
	 * @return string
	 */
	protected function getMockExtension() {
		$structure = [
			'flux' => [
				'Resources' => [
					'Private' => [
						'Templates' => [
							'Content' => [
								'TestTrue.html' => 'Test template with Icon available',
								'TestFalse.html' => 'Test template with Icon not available'
							]
						]
					],
					'Public' => [
						'Icons' => [
							'Content' => [
								'TestTrue.png' => 'Test-Icon'
							]
						]
					]
				],
			]
		];
		vfsStream::setup('ext', NULL, $structure);
		$vfsUrl = vfsStream::url('ext');

		return $vfsUrl;
	}

}
