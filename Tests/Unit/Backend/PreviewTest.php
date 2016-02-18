<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * PreviewTest
 */
class PreviewTest extends AbstractTestCase {

	/**
	 * Setup
	 */
	public function setUp() {
		$configurationManager = $this->getMock('FluidTYPO3\Flux\Configuration\ConfigurationManager');
		$fluxService = $this->objectManager->get('FluidTYPO3\Flux\Service\FluxService');
		$fluxService->injectConfigurationManager($configurationManager);
		$GLOBALS['TYPO3_DB'] = $this->getMock('TYPO3\\CMS\\Core\\Database\\DatabaseConnection', array('exec_SELECTgetRows'), array(), '', FALSE);
		$GLOBALS['TYPO3_DB']->expects($this->any())->method('exec_SELECTgetRows')->willReturn(array());
		$tempFiles = (array) glob(GeneralUtility::getFileAbsFileName('typo3temp/flux-preview-*.tmp'));
		foreach ($tempFiles as $tempFile) {
			if (TRUE === file_exists($tempFile)) {
				unlink($tempFile);
			}
		}
	}

	/**
	 * @test
	 */
	public function canExecuteRenderer() {
		$caller = $this->getMock('TYPO3\CMS\Backend\View\PageLayoutView', array('attachAssets'), array(), '', FALSE);
		$function = 'FluidTYPO3\Flux\Backend\Preview';
		$result = $this->callUserFunction($function, $caller);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function canGenerateShortcutIconAndLink() {
		$className = 'FluidTYPO3\Flux\Backend\Preview';
		$instance = $this->getMock($className, array('getPageTitleAndPidFromContentUid', 'attachAssets'));
		$instance->expects($this->once())->method('getPageTitleAndPidFromContentUid')->with(1)->will($this->returnValue(array('pid' => 1, 'title' => 'test')));
		$headerContent = '';
		$itemContent = '';
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
		$result = $this->callInaccessibleMethod($instance, 'getPageTitleAndPidFromContentUid', 1);
		$this->assertEmpty($result);
	}

	/**
	 * @test
	 */
	public function stopsRenderingWhenProviderSaysStop() {
		$instance = $this->getMock('FluidTYPO3\Flux\Backend\Preview', array('createShortcutIcon', 'attachAssets'));
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
		$instance = $this->getMock($function, array('attachAssets'));
		$instance->preProcess($caller, $drawItem, $headerContent, $itemContent, $row);
		Core::unregisterConfigurationProvider('FluidTYPO3\Flux\Tests\Fixtures\Classes\DummyConfigurationProvider');
	}

}
