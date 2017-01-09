<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractRelationFormField;

/**
 * Tree
 */
class Tree extends AbstractRelationFormField
{

    const DEFAULT_ALLOW_RECURSIVE_MODE = false;
    const DEFAULT_EXPAND_ALL = false;
    const DEFAULT_NON_SELECTABLE_LEVELS  = '0';
    const DEFAULT_MAX_LEVELS = 2;
    const DEFAULT_SHOW_HEADER = false;
    const DEFAULT_WIDTH = 280;

    /**
     * @var string
     */
    protected $parentField;

    /**
     * @var boolean
     */
    protected $allowRecursiveMode = self::DEFAULT_ALLOW_RECURSIVE_MODE;

    /**
     * @var boolean
     */
    protected $expandAll = self::DEFAULT_EXPAND_ALL;

    /**
     * @var string
     */
    protected $nonSelectableLevels = self::DEFAULT_NON_SELECTABLE_LEVELS;

    /**
     * @var integer
     */
    protected $maxLevels = self::DEFAULT_MAX_LEVELS;

    /**
     * @var boolean
     */
    protected $showHeader = self::DEFAULT_SHOW_HEADER;

    /**
     * @var integer
     */
    protected $width = self::DEFAULT_WIDTH;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('select');
        $configuration['renderMode'] = 'tree';
        $configuration['treeConfig'] = [
            'parentField' => $this->getParentField(),
            'appearance' => [
                'allowRecursiveMode' => $this->getAllowRecursiveMode(),
                'expandAll' => $this->getExpandAll(),
                'nonSelectableLevels' => $this->getNonSelectableLevels(),
                'maxLevels' => $this->getMaxLevels(),
                'showHeader' => $this->getShowHeader(),
                'width' => $this->getWidth(),
            ],
        ];
        return $configuration;
    }

    /**
     * @param string $parentField
     * @return Tree
     */
    public function setParentField($parentField)
    {
        $this->parentField = $parentField;
        return $this;
    }

    /**
     * @return string
     */
    public function getParentField()
    {
        return $this->parentField;
    }

    /**
     * @param boolean $allowRecursiveMode
     * @return Tree
     */
    public function setAllowRecursiveMode($allowRecursiveMode)
    {
        $this->allowRecursiveMode = $allowRecursiveMode;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getAllowRecursiveMode()
    {
        return $this->allowRecursiveMode;
    }

    /**
     * @param boolean $expandAll
     * @return Tree
     */
    public function setExpandAll($expandAll)
    {
        $this->expandAll = $expandAll;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getExpandAll()
    {
        return $this->expandAll;
    }

    /**
     * @param string $nonSelectableLevels
     * @return Tree
     */
    public function setNonSelectableLevels($nonSelectableLevels)
    {
        $this->nonSelectableLevels = $nonSelectableLevels;
        return $this;
    }

    /**
     * @return string
     */
    public function getNonSelectableLevels()
    {
        return $this->nonSelectableLevels;
    }

    /**
     * @param integer $maxLevels
     * @return Tree
     */
    public function setMaxLevels($maxLevels)
    {
        $this->maxLevels = $maxLevels;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxLevels()
    {
        return $this->maxLevels;
    }

    /**
     * @param boolean $showHeader
     * @return Tree
     */
    public function setShowHeader($showHeader)
    {
        $this->showHeader = $showHeader;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowHeader()
    {
        return $this->showHeader;
    }

    /**
     * @param integer $width
     * @return Tree
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }
}
