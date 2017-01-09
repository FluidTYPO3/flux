<?php
namespace FluidTYPO3\Flux\Form\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractWizard;

/**
 * List wizard
 *
 * Note: named "ListWizard" due to restriction disallowing classes named "Wizard"
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 */
class ListWizard extends AbstractWizard
{

    /**
     * @var string
     */
    protected $name = 'list';

    /**
     * @var string
     */
    protected $type = 'popup';

    /**
     * @var string
     */
    protected $icon = 'list.gif';

    /**
     * @var array
     */
    protected $module = [
        'name' => 'wizard_list'
    ];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var integer
     */
    protected $height = 500;

    /**
     * @var integer
     */
    protected $width = 400;

    /**
     * @var integer
     */
    protected $storagePageUid;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $structure = [
            'JSopenParams' => sprintf(
                'height=%d,width=%d,status=0,menubar=0,scrollbars=1',
                $this->getHeight(),
                $this->getWidth()
            ),
            'params' => [
                'table' => $this->getTable(),
                'pid' => $this->getStoragePageUid(),
            ]
        ];
        return $structure;
    }

    /**
     * @param integer $height
     * @return ListWizard
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param integer $width
     * @return ListWizard
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

    /**
     * @param integer $storagePageUid
     * @return ListWizard
     */
    public function setStoragePageUid($storagePageUid)
    {
        $this->storagePageUid = $storagePageUid;
        return $this;
    }

    /**
     * @return integer
     */
    public function getStoragePageUid()
    {
        return $this->storagePageUid;
    }

    /**
     * @param string $table
     * @return ListWizard
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
