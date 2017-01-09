<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractMultiValueFormField;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * File
 */
class File extends AbstractMultiValueFormField
{

    /**
     * @var string
     */
    protected $disallowed = '';

    /**
     * @var string
     */
    protected $allowed = '';

    /**
     * @var integer
     */
    protected $maxSize;

    /**
     * @var string
     */
    protected $uploadFolder;

    /**
     * @var boolean
     */
    protected $showThumbnails = false;

    /**
     * @var boolean
     */
    protected $useFalRelation = false;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('group');
        $configuration['disallowed'] = $this->getDisallowed();
        $configuration['allowed'] = $this->getAllowed();
        $configuration['max_size'] = $this->getMaxSize();
        $configuration['uploadfolder'] = $this->getUploadFolder();
        $configuration['show_thumbs'] = $this->getShowThumbnails();
        $configuration['internal_type'] = 'file_reference';

        if ($this->getUseFalRelation() === true) {
            $configuration['internal_type'] = 'db';
            $configuration['allowed'] = 'sys_file';
            $configuration['appearance'] = [
                'elementBrowserAllowed' => $this->getAllowed() ? $this->getAllowed() : '*',
                'elementBrowserType' => 'file'
            ];
        }
        return $configuration;
    }

    /**
     * Overrides parent method to ensure properly formatted
     * default values for files
     *
     * @param mixed $default
     * @return \FluidTYPO3\Flux\Form\FieldInterface
     */
    public function setDefault($default)
    {
        if (null !== $default) {
            $files = [];
            $filePaths = GeneralUtility::trimExplode(',', $default);
            foreach ($filePaths as $path) {
                if (false === strpos($path, '|')) {
                    $files[] = $path . '|' . rawurlencode($path);
                } else {
                    $files[] = $path;
                }
            }
            $default = implode(',', $files);
        }
        $this->default = $default;
        return $this;
    }

    /**
     * @param string $allowed
     * @return File
     */
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;
        return $this;
    }

    /**
     * @return string
     */
    public function getAllowed()
    {
        return $this->allowed;
    }

    /**
     * @param string $disallowed
     * @return File
     */
    public function setDisallowed($disallowed)
    {
        $this->disallowed = $disallowed;
        return $this;
    }

    /**
     * @return string
     */
    public function getDisallowed()
    {
        return $this->disallowed;
    }

    /**
     * @param integer $maxSize
     * @return File
     */
    public function setMaxSize($maxSize)
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }

    /**
     * @param string $uploadFolder
     * @return File
     */
    public function setUploadFolder($uploadFolder)
    {
        $this->uploadFolder = $uploadFolder;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadFolder()
    {
        return $this->uploadFolder;
    }

    /**
     * @param boolean $showThumbnails
     * @return File
     */
    public function setShowThumbnails($showThumbnails)
    {
        $this->showThumbnails = (boolean) $showThumbnails;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowThumbnails()
    {
        return (boolean) $this->showThumbnails;
    }

    /**
     * @param boolean $useFalRelation
     * @return File
     */
    public function setUseFalRelation($useFalRelation)
    {
        $this->useFalRelation = $useFalRelation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseFalRelation()
    {
        return $this->useFalRelation;
    }
}
