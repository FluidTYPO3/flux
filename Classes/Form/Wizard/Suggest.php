<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Suggest wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 */
class Suggest extends AbstractWizard
{

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
    protected $icon = null;

    /**
     * @var array
     */
    protected $module = null;

    /**
     * @var string
     */
    protected $table = 'pages';

    /**
     * @var array
     */
    protected $storagePageUids = [];

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
    protected $searchWholePhrase = false;

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
    public function buildConfiguration()
    {
        $table = $this->getTable();
        $configuration = [
            'type' => 'suggest',
            $table => [
                'table' => $table,
                'pidList' => implode(',', $this->getStoragePageUids()),
                'pidDepth' => $this->getStoragePageRecursiveDepth(),
                'minimumCharacters' => $this->getMinimumCharacters(),
                'maxPathTitleLength' => $this->getMaxPathTitleLength(),
                'searchWholePhrase' => intval($this->getSearchWholePhrase()),
                'searchCondition' => $this->getSearchCondition(),
                'cssClass' => $this->getCssClass(),
                'receiverClass' => $this->getReceiverClass(),
                'renderFunc' => $this->getRenderFunction(),
            ],
        ];
        return $configuration;
    }

    /**
     * @return string
     */
    public function getName()
    {
        $name = $this->name;
        return $name;
    }

    /**
     * @param string $cssClass
     * @return Suggest
     */
    public function setCssClass($cssClass)
    {
        $this->cssClass = $cssClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        return $this->cssClass;
    }

    /**
     * @param integer $maxPathTitleLength
     * @return Suggest
     */
    public function setMaxPathTitleLength($maxPathTitleLength)
    {
        $this->maxPathTitleLength = $maxPathTitleLength;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxPathTitleLength()
    {
        return $this->maxPathTitleLength;
    }

    /**
     * @param integer $minimumCharacters
     * @return Suggest
     */
    public function setMinimumCharacters($minimumCharacters)
    {
        $this->minimumCharacters = $minimumCharacters;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMinimumCharacters()
    {
        return $this->minimumCharacters;
    }

    /**
     * @param string $receiverClass
     * @return Suggest
     */
    public function setReceiverClass($receiverClass)
    {
        $this->receiverClass = $receiverClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getReceiverClass()
    {
        return $this->receiverClass;
    }

    /**
     * @param string $renderFunction
     * @return Suggest
     */
    public function setRenderFunction($renderFunction)
    {
        $this->renderFunction = $renderFunction;
        return $this;
    }

    /**
     * @return string
     */
    public function getRenderFunction()
    {
        return $this->renderFunction;
    }

    /**
     * @param string $searchCondition
     * @return Suggest
     */
    public function setSearchCondition($searchCondition)
    {
        $this->searchCondition = $searchCondition;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchCondition()
    {
        return $this->searchCondition;
    }

    /**
     * @param boolean $searchWholePhrase
     * @return Suggest
     */
    public function setSearchWholePhrase($searchWholePhrase)
    {
        $this->searchWholePhrase = $searchWholePhrase;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getSearchWholePhrase()
    {
        return $this->searchWholePhrase;
    }

    /**
     * @return Suggest
     * @param integer $storagePageRecursiveDepth
     */
    public function setStoragePageRecursiveDepth($storagePageRecursiveDepth)
    {
        $this->storagePageRecursiveDepth = $storagePageRecursiveDepth;
        return $this;
    }

    /**
     * @return integer
     */
    public function getStoragePageRecursiveDepth()
    {
        return $this->storagePageRecursiveDepth;
    }

    /**
     * @param array $storagePageUids
     * @return Suggest
     */
    public function setStoragePageUids($storagePageUids)
    {
        if (false === is_array($storagePageUids)) {
            $this->storagePageUids = GeneralUtility::trimExplode(',', $storagePageUids);
        } else {
            $this->storagePageUids = $storagePageUids;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getStoragePageUids()
    {
        return $this->storagePageUids;
    }

    /**
     * @param string $table
     * @return Suggest
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
}
