<?php
namespace FluidTYPO3\Flux\Form\Wizard;
/*****************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Claus Due <claus@namelesscoder.net>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
 *****************************************************************/

use FluidTYPO3\Flux\Form\AbstractWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package Flux
 * @subpackage Form\Wizard
 */
class Suggest extends AbstractWizard {

	/**
	 * @var string
	 */
	protected $name = 'suggest';

	/**
	 * @var string
	 */
	protected $type = 'suggest';

	/**
	 * @var string
	 */
	protected $icon = NULL;

	/**
	 * @var string
	 */
	protected $script = NULL;

	/**
	 * @var string
	 */
	protected $table = 'pages';

	/**
	 * @var array
	 */
	protected $storagePageUids = array();

	/**
	 * @var integer
	 */
	protected $storagePageRecursiveDepth = 99;

	/**
	 * @var integer
	 */
	protected $minimumCharacters = 1;

	/**
	 * Maximum path segment length - crops titles over this length
	 * @var integer
	 */
	protected $maxPathTitleLength = 15;

	/**
	 * A match requires a full word that matches the search value
	 * @var boolean
	 */
	protected $searchWholePhrase = FALSE;

	/**
	 * Search condition - for example, if table is pages "doktype = 1" to only allow standard pages
	 * @var string
	 */
	protected $searchCondition = '';

	/**
	 * Use this CSS class for all list items
	 * @var string
	 */
	protected $cssClass = '';

	/**
	 * Class reference, target class should be derived from "t3lib_tceforms_suggest_defaultreceiver"
	 * @var string
	 */
	protected $receiverClass = '';

	/**
	 * Reference to function which processes all records displayed in results
	 * @var string
	 */
	protected $renderFunction = '';

	/**
	 * @return array
	 */
	public function buildConfiguration() {
		$table = $this->getTable();
		$configuration = array(
			'type' => 'suggest',
			$table => array(
				'table' => $table,
				'pidList' => implode(',', $this->getStoragePageUids()),
				'pidDepth' => $this->getStoragePageRecursiveDepth(),
				'minimumCharacters' => $this->getMinimumCharacters(),
				'maxPathTitleLength'=> $this->getMaxPathTitleLength(),
				'searchWholePhrase' => intval($this->getSearchWholePhrase()),
				'searchCondition' => $this->getSearchCondition(),
				'cssClass' => $this->getCssClass(),
				'receiverClass' => $this->getReceiverClass(),
				'renderFunc' => $this->getRenderFunction(),
			),
		);
		return $configuration;
	}

	/**
	 * @return string
	 */
	public function getName() {
		$table = $this->getTable();
		$name = $this->name . $table;
		return $name;
	}

	/**
	 * @param string $cssClass
	 * @return Suggest
	 */
	public function setCssClass($cssClass) {
		$this->cssClass = $cssClass;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCssClass() {
		return $this->cssClass;
	}

	/**
	 * @param integer $maxPathTitleLength
	 * @return Suggest
	 */
	public function setMaxPathTitleLength($maxPathTitleLength) {
		$this->maxPathTitleLength = $maxPathTitleLength;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMaxPathTitleLength() {
		return $this->maxPathTitleLength;
	}

	/**
	 * @param integer $minimumCharacters
	 * @return Suggest
	 */
	public function setMinimumCharacters($minimumCharacters) {
		$this->minimumCharacters = $minimumCharacters;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getMinimumCharacters() {
		return $this->minimumCharacters;
	}

	/**
	 * @param string $receiverClass
	 * @return Suggest
	 */
	public function setReceiverClass($receiverClass) {
		$this->receiverClass = $receiverClass;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getReceiverClass() {
		return $this->receiverClass;
	}

	/**
	 * @param string $renderFunction
	 * @return Suggest
	 */
	public function setRenderFunction($renderFunction) {
		$this->renderFunction = $renderFunction;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRenderFunction() {
		return $this->renderFunction;
	}

	/**
	 * @param string $searchCondition
	 * @return Suggest
	 */
	public function setSearchCondition($searchCondition) {
		$this->searchCondition = $searchCondition;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSearchCondition() {
		return $this->searchCondition;
	}

	/**
	 * @param boolean $searchWholePhrase
	 * @return Suggest
	 */
	public function setSearchWholePhrase($searchWholePhrase) {
		$this->searchWholePhrase = $searchWholePhrase;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getSearchWholePhrase() {
		return $this->searchWholePhrase;
	}

	/**
	 * @return Suggest
	 * @param integer $storagePageRecursiveDepth
	 */
	public function setStoragePageRecursiveDepth($storagePageRecursiveDepth) {
		$this->storagePageRecursiveDepth = $storagePageRecursiveDepth;
		return $this;
	}

	/**
	 * @return integer
	 */
	public function getStoragePageRecursiveDepth() {
		return $this->storagePageRecursiveDepth;
	}

	/**
	 * @param array $storagePageUids
	 * @return Suggest
	 */
	public function setStoragePageUids($storagePageUids) {
		if (FALSE === is_array($storagePageUids)) {
			$this->storagePageUids = GeneralUtility::trimExplode(',', $storagePageUids);
		} else {
			$this->storagePageUids = $storagePageUids;
		}
		return $this;
	}

	/**
	 * @return array
	 */
	public function getStoragePageUids() {
		return $this->storagePageUids;
	}

	/**
	 * @param string $table
	 * @return Suggest
	 */
	public function setTable($table) {
		$this->table = $table;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTable() {
		return $this->table;
	}

}
