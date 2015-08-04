<?php
namespace FluidTYPO3\Flux\Tests\Functional\Service;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

require_once 'typo3/sysext/core/Tests/Functional/DataHandling/AbstractDataHandlerActionTestCase.php';

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Tests\Functional\DataHandling\AbstractDataHandlerActionTestCase;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;


/**
 *
 *
 * @author Andreas Wolf <aw@foundata.net>
 */
class ContentServiceTest extends AbstractDataHandlerActionTestCase {

	/**
	 * @var array
	 */
	protected $testExtensionsToLoad = array('typo3conf/ext/flux');

	protected $scenarioDataSetDirectory = 'typo3conf/ext/flux/Tests/Functional/Service/DataSet/';

	const PAGE_ID_MAIN = 100;
	const PAGE_ID_TARGET = 101;
	const FLUIDCONTENT_CONTAINER_ID = 200;
	const FLUIDCONTENT_CONTENT_ID = 201;
	const CONTENT_ID_ABOVE = 203;
	const CONTENT_ID_BELOW = 202;
	const FLUIDCONTENT_NESTED_OUTER_CONTAINER_ID = 300;
	const FLUIDCONTENT_NESTED_INNER_CONTAINER_ID = 301;
	const FLUIDCONTENT_NESTED_INNER_CONTENT_ID = 302;

	public function setUp() {
		parent::setUp();

		$this->importScenarioDataSet('DefaultElements');

		$this->setUpFrontendRootPage(self::PAGE_ID_MAIN, array('typo3/sysext/core/Tests/Functional/Fixtures/Frontend/JsonRenderer.ts'));
	}

	/**
	 * Sets $_GET['CB'] (ClipBoard) to initiate a paste operation of the records processed by DataHandler to
	 * the given page and position.
	 *
	 * @param string $table The table to paste the contents in
	 * @param int $targetPageId The target page id.
	 * @param int $relatedContentId The ID of the related element (= the element to paste after). If 0, element is inserted at the top of the column.
	 * @param int $parentUid The ID of the parent (Flux) element to insert to.
	 * @param string $targetArea The area in the parent element the new element should be inserted to.
	 * @param string|int $targetColumn The target column. Is only used if parent uid/target area are not set (?)
	 */
	protected function createGetEntryForClipboardPasteOperation($table, $targetPageId, $relatedContentId = 0, $parentUid = 0, $targetArea = '', $targetColumn = 0) {
		$_GET['CB'] = array(
			'paste' => $table . '|'
				. implode('-', array(
					$targetPageId,
					'paste',
					$relatedContentId,
					$parentUid,
					$targetArea,
					$targetColumn,
				))
		);
	}

	/**
	 * @param int $targetPageId
	 * @param int $targetContentId
	 */
	protected function pasteContentAfterOtherContentElement($targetPageId, $targetContentId) {
		$record = BackendUtility::getRecord('tt_content', $targetContentId);
		if ($record['tx_flux_parent'] > 0) {
			$this->createGetEntryForClipboardPasteOperation('tt_content', $targetPageId, $targetContentId, $record['tx_flux_parent'], $record['tx_flux_column']);
		} else {
			$this->createGetEntryForClipboardPasteOperation('tt_content', $targetPageId, $targetContentId);
		}
	}

	/**
	 * @param int $targetPageId
	 */
	protected function pasteContentAtBeginningOfPage($targetPageId) {
		$this->createGetEntryForClipboardPasteOperation('tt_content', $targetPageId, 0);
	}

	/**
	 * @param int $targetPageId
	 * @param int $columnId
	 */
	protected function pasteContentToColumn($targetPageId, $columnId) {
		$this->createGetEntryForClipboardPasteOperation('tt_content', $targetPageId, 0, 0, '', $columnId);
	}

	/**
	 * @param int $targetPageId
	 * @param int $targetContainerElementId
	 * @param string $targetColumnId
	 */
	protected function pasteContentToFluidcontentColumn($targetPageId, $targetContainerElementId, $targetColumnId) {
		$this->createGetEntryForClipboardPasteOperation('tt_content', $targetPageId, 0, $targetContainerElementId, $targetColumnId);
	}

	/**
	 * @param int $column
	 * @param array $actualRecord
	 */
	protected function assertContentInColumn($column, $actualRecord) {
		$this->assertEquals($column, $actualRecord['colPos'], 'Element is not in correct column');
	}

	/**
	 * @param array $content The content that should be sorted below the next content
	 * @param array $contentAbove
	 */
	protected function assertContentIsSortedBelowOtherContent($content, $contentAbove) {
		$this->assertGreaterThan($contentAbove['sorting'], $content['sorting']);
	}

	/**
	 * Tests if a record is not inside a flux element.
	 *
	 * @param array $actualRecord
	 */
	protected function assertContentNotInFluxElement($actualRecord) {
		// only test if colPos is not 18181 (the Flux colPos value) because colPos might be any other value
		$this->assertNotEquals(18181, $actualRecord['colPos'], 'Element is in Flux colPos');
		$this->assertEquals(0, $actualRecord['tx_flux_parent'], 'Element is in Flux container');
		$this->assertSame('', $actualRecord['tx_flux_column'], 'Element is in a column inside a Flux container');
	}

	/**
	 * Tests if a content record is in the right column inside the given flux element.
	 *
	 * @param int $expectedParentId
	 * @param string $expectedColumn
	 * @param array $actualRecord
	 */
	protected function assertContentInFluxElement($expectedParentId, $expectedColumn, $actualRecord) {
		$this->assertEquals(18181, $actualRecord['colPos'], 'Element is in Flux column');
		$this->assertEquals($expectedParentId, $actualRecord['tx_flux_parent'], 'Element is in Flux container');
		$this->assertSame($expectedColumn, $actualRecord['tx_flux_column'], 'Element is in Flux column');
	}

	/**
	 * @test
	 */
	public function copyFromInsideFluidcontentElementToPageAfterFluidcontentElement() {
		$this->pasteContentAfterOtherContentElement(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTAINER_ID);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTENT_ID, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertNotEmpty($newContentId);

		$containerRecord = BackendUtility::getRecord('tt_content', self::FLUIDCONTENT_CONTAINER_ID);
		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentIsSortedBelowOtherContent($newRecord, $containerRecord);
		$this->assertContentNotInFluxElement($newRecord);
	}

	/**
	 * @test
	 */
	public function copyBetweenColumnsInFluidcontentElement() {
		$this->pasteContentToFluidcontentColumn(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTAINER_ID, 'column2');
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTENT_ID, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertGreaterThan(0, $newContentId);

		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'column2', $newRecord);
	}

	/**
	 * @test
	 */
	public function copyFromFluidcontentColumnToAfterSameElemeent() {
		$this->pasteContentAfterOtherContentElement(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTENT_ID);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTENT_ID, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertGreaterThan(0, $newContentId);

		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'headline', $newRecord);
	}

	/**
	 * @test
	 */
	public function copyFromPageToColumnInFluidcontentElement() {
		$this->pasteContentToFluidcontentColumn(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTAINER_ID, 'column1');
		$mappingArray = $this->actionService->copyRecord('tt_content', self::CONTENT_ID_BELOW, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::CONTENT_ID_BELOW];
		$this->assertGreaterThan(0, $newContentId);

		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'column1', $newRecord);
	}

	/**
	 * @test
	 */
	public function copyFromPageToPositionAtTopOfFluidcontentColumn() {
		$this->pasteContentToFluidcontentColumn(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTAINER_ID, 'headline');
		$mappingArray = $this->actionService->copyRecord('tt_content', self::CONTENT_ID_BELOW, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::CONTENT_ID_BELOW];
		$this->assertNotEmpty($newContentId);

		$existingRecord = BackendUtility::getRecord('tt_content', self::FLUIDCONTENT_CONTENT_ID);
		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentIsSortedBelowOtherContent($existingRecord, $newRecord);
		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'headline', $newRecord);
	}

	/**
	 * @test
	 */
	public function copyFromPageToPositionAfterElementInFluidcontentColumn() {
		$this->pasteContentAfterOtherContentElement(self::PAGE_ID_MAIN, self::FLUIDCONTENT_CONTENT_ID);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::CONTENT_ID_ABOVE, self::PAGE_ID_MAIN);

		$newContentId = $mappingArray['tt_content'][self::CONTENT_ID_ABOVE];
		$this->assertNotEmpty($newContentId);

		$existingRecord = BackendUtility::getRecord('tt_content', self::FLUIDCONTENT_CONTENT_ID);
		$newRecord = BackendUtility::getRecord('tt_content', $newContentId);

		//$this->assertFalse(print_r($newRecord, TRUE));
		//$this->assertFalse($newRecord['header'] . ' ' . $newRecord['sorting'] . ' ' . $existingRecord['sorting']);
		$this->assertContentIsSortedBelowOtherContent($newRecord, $existingRecord);
		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'headline', $newRecord);
	}

	/**
	 * @test
	 */
	public function copyFluidcontentElementToOtherColumnOnSamePage() {
		$columnId = 1;
		$this->pasteContentToColumn(self::PAGE_ID_TARGET, $columnId);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTAINER_ID, self::PAGE_ID_MAIN);

		$newContainerId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTAINER_ID];
		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertNotEmpty($newContainerId);
		$this->assertNotEmpty($newContentId);

		$newContainerRecord = BackendUtility::getRecord('tt_content', $newContainerId);
		$newContentRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentInColumn($columnId, $newContainerRecord);
		$this->assertContentInFluxElement($newContainerRecord['uid'], 'headline', $newContentRecord);
	}

	/**
	 * @test
	 */
	public function copyFluidcontentElementToColumnInOtherFluidcontentElement() {
		$this->pasteContentToFluidcontentColumn(self::PAGE_ID_TARGET, self::FLUIDCONTENT_CONTAINER_ID, 'targetColumn');
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTAINER_ID, self::PAGE_ID_TARGET);

		$newContainerId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTAINER_ID];
		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertNotEmpty($newContainerId);
		$this->assertNotEmpty($newContentId);

		$newContainerRecord = BackendUtility::getRecord('tt_content', $newContainerId);
		$newContentRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentInFluxElement(self::FLUIDCONTENT_CONTAINER_ID, 'targetColumn', $newContainerRecord);
		$this->assertContentInFluxElement($newContainerRecord['uid'], 'headline', $newContentRecord);
	}

	/**
	 * @test
	 */
	public function copyFluidcontentElementToDifferentPage() {
		$this->pasteContentAtBeginningOfPage(self::PAGE_ID_TARGET);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_CONTAINER_ID, self::PAGE_ID_TARGET);

		$newContainerId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTAINER_ID];
		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_CONTENT_ID];
		$this->assertNotEmpty($newContainerId);
		$this->assertNotEmpty($newContentId);

		$newContainerRecord = BackendUtility::getRecord('tt_content', $newContainerId);
		$newContentRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentNotInFluxElement($newContainerRecord);
		$this->assertContentInFluxElement($newContainerRecord['uid'], 'headline', $newContentRecord);
	}

	/**
	 * @test
	 */
	public function copyNestedFluidcontentElementToDifferentPage() {
		$this->pasteContentAtBeginningOfPage(self::PAGE_ID_TARGET);
		$mappingArray = $this->actionService->copyRecord('tt_content', self::FLUIDCONTENT_NESTED_OUTER_CONTAINER_ID, self::PAGE_ID_TARGET);

		$newOuterContainerId = $mappingArray['tt_content'][self::FLUIDCONTENT_NESTED_OUTER_CONTAINER_ID];
		$newInnerContainerId = $mappingArray['tt_content'][self::FLUIDCONTENT_NESTED_INNER_CONTAINER_ID];
		$newContentId = $mappingArray['tt_content'][self::FLUIDCONTENT_NESTED_INNER_CONTENT_ID];
		$this->assertNotEmpty($newOuterContainerId);
		$this->assertNotEmpty($newContentId);

		$newOuterContainerRecord = BackendUtility::getRecord('tt_content', $newOuterContainerId);
		$newInnerContainerRecord = BackendUtility::getRecord('tt_content', $newInnerContainerId);
		$newContentRecord = BackendUtility::getRecord('tt_content', $newContentId);

		$this->assertContentNotInFluxElement($newOuterContainerRecord);
		$this->assertContentInFluxElement($newOuterContainerRecord['uid'], 'column1', $newInnerContainerRecord);
		$this->assertContentInFluxElement($newInnerContainerRecord['uid'], 'headline', $newContentRecord);
	}

}
 