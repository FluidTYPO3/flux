<?php
namespace FluidTYPO3\Flux\Backend;
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

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class PreviewTest extends AbstractTestCase {

	/**
	 * Setup
	 */
	public function setup() {
		$tempFiles = (array) glob(GeneralUtility::getFileAbsFileName('typo3temp/flux-preview-*.tmp'));
		foreach ($tempFiles as $tempFile) {
			unlink($tempFile);
		}
	}

	/**
	 * @test
	 */
	public function canExecuteRenderer() {
		$caller = $this->objectManager->get('TYPO3\CMS\Backend\View\PageLayoutView');
		$function = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['flux'];
		$this->callUserFunction($function, $caller);
	}

	/**
	 * @test
	 */
	public function canGenerateShortcutIconAndLink() {
		$className = 'FluidTYPO3\Flux\Backend\Preview';
		$instance = $this->getMock($className, array('getPageTitleAndPidFromContentUid'));
		$instance->expects($this->once())->method('getPageTitleAndPidFromContentUid')->with(1)->will($this->returnValue(array('pid' => 1, 'title' => 'test')));
		$headerContent = $itemContent = '';
		$drawItem = TRUE;
		$row = array('uid' => 1, 'CType' => 'shortcut', 'records' => 1);
		$this->setup();
		$instance->renderPreview($headerContent, $itemContent, $row, $drawItem);
		$this->assertContains('href="?id=1#c1"', $itemContent);
		$this->assertContains('<span class="t3-icon t3-icon-actions-insert t3-icon-insert-reference t3-icon-actions t3-icon-actions-insert-reference"></span>', $itemContent);
	}

	/**
	 * @test
	 */
	public function canGetPageTitleAndPidFromContentUid() {
		$className = 'FluidTYPO3\Flux\Backend\Preview';
		$instance = $this->getMock($className);
		$this->callInaccessibleMethod($instance, 'getPageTitleAndPidFromContentUid', 1);
	}

	/**
	 * @test
	 */
	public function stopsRenderingWhenProviderSaysStop() {
		$instance = $this->getMock('FluidTYPO3\Flux\Backend\Preview', array('createShortcutIcon'));
		$instance->expects($this->never())->method('createShortcutIcon');
		$configurationServiceMock = $this->getMock('FluidTYPO3\Flux\Service\FluxService', array('resolveConfigurationProviders'));
		$providerOne = $this->getMock('FluidTYPO3\Flux\Provider\ContentProvider', array('getPreview'));
		$providerOne->expects($this->once())->method('getPreview')->will($this->returnValue(array('test', 'test', FALSE)));
		$providerTwo = $this->getMock('FluidTYPO3\Flux\Provider\ContentProvider', array('getPreview'));
		$providerTwo->expects($this->never())->method('getPreview');
		$configurationServiceMock->expects($this->once())->method('resolveConfigurationProviders')->will($this->returnValue(array($providerOne, $providerTwo)));
		ObjectAccess::setProperty($instance, 'configurationService', $configurationServiceMock, TRUE);
		$header = 'test';
		$item = 'test';
		$record = Records::$contentRecordIsParentAndHasChildren;
		$draw = TRUE;
		$this->setup();
		$instance->renderPreview($header, $item, $record, $draw);
	}

	/**
	 * @param string $function
	 * @param mixed $caller
	 */
	protected function callUserFunction($function, $caller) {
		$drawItem = TRUE;
		$headerContent = '';
		$itemContent = '';
		$row = Records::$contentRecordWithoutParentAndWithoutChildren;
		$row['pi_flexform'] = Xml::SIMPLE_FLEXFORM_SOURCE_DEFAULT_SHEET_ONE_FIELD;
		Core::registerConfigurationProvider('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
		$instance = $this->objectManager->get($function);
		$instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
		Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
	}

}
