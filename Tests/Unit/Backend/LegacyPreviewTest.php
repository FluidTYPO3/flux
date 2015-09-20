<?php
namespace FluidTYPO3\Flux\Tests\Unit\Backend;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Backend\LegacyPreview;
use FluidTYPO3\Flux\Core;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Records;
use FluidTYPO3\Flux\Tests\Fixtures\Data\Xml;
use FluidTYPO3\Flux\Tests\Unit\AbstractTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package Flux
 */
class LegacyPreviewTest extends AbstractTestCase {

	/**
	 * @test
	 */
	public function testAttachAssets() {
		$pageRenderer = $this->getMock('TYPO3\\CMS\\Core\\Page\\PageRenderer', array('addCssFile', 'addJsFile'));
		$pageRenderer->expects($this->atLeastOnce())->method('addCssFile');
		$pageRenderer->expects($this->atLeastOnce())->method('addJsFile');

		$template = $this->getAccessibleMock(
			'TYPO3\\CMS\\Backend\\Template\\DocumentTemplate',
			array('getPageRenderer'),
			array(), '', FALSE
		);
		$template->expects($this->atLeastOnce())->method('getPageRenderer')->willReturn($pageRenderer);

		$subject = $this->getAccessibleMock('FluidTYPO3\\Flux\\Backend\\LegacyPreview', array('getDocumentTemplate'));
		$subject->expects($this->once())->method('getDocumentTemplate')->willReturn($template);
		$subject->_setStatic('assetsIncluded', FALSE);
		$this->callInaccessibleMethod($subject, 'attachAssets');
	}

	/**
	 * @test
	 */
	public function testGetDocumentTemplate() {
		$subject = new LegacyPreview();
		$result = $this->callInaccessibleMethod($subject, 'getDocumentTemplate');
		$this->assertInstanceOf('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate', $result);
	}

}
