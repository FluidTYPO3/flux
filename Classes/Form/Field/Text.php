<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\FieldInterface;

/**
 * Text
 */
class Text extends Input implements FieldInterface
{
    /**
     * @var integer
     */
    protected $columns = 85;

    /**
     * @var integer
     */
    protected $rows = 10;

    /**
     * @var string
     */
    protected $defaultExtras;

    /**
     * @var boolean
     */
    protected $enableRichText = false;

    /**
     * @var string
     */
    protected $richtextConfiguration;

    /**
     * @var string
     */
    protected $renderType = '';

    /**
     * @var string
     */
    protected $format;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('text');
        $configuration['rows'] = $this->getRows();
        $configuration['cols'] = $this->getColumns();
        $configuration['eval'] = $this->getValidate();
        if (true === $this->getEnableRichText()) {
            $configuration['enableRichtext'] = true;
            if ($this->isCkEditorActive()) {
                $configuration['richtextConfiguration'] = $this->getRichtextConfiguration();
            } else {
                //rtehtmlarea
                $defaultExtras = $this->getDefaultExtras();
                if (true === empty($defaultExtras)) {
                    $typoScript = $this->getConfigurationService()->getAllTypoScript();
                    $configuration['defaultExtras'] = $typoScript['plugin']['tx_flux']['settings']['flexform']['rteDefaults'];
                } else {
                    $configuration['defaultExtras'] = $defaultExtras;
                }
            }
        } else {
            $configuration['defaultExtras'] = $this->getDefaultExtras();
        }
        $renderType = $this->getRenderType();
        if (false === empty($renderType)) {
            $configuration['renderType'] = $renderType;
            $configuration['format'] = $this->getFormat();
        }

        //var_dump($configuration);die();

        return $configuration;
    }

    /**
     * @param integer $columns
     * @return Text
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return integer
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @param string $defaultExtras
     * @return Text
     */
    public function setDefaultExtras($defaultExtras)
    {
        $this->defaultExtras = $defaultExtras;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultExtras()
    {
        return $this->defaultExtras;
    }

    /**
     * @param boolean $enableRichText
     * @return Text
     */
    public function setEnableRichText($enableRichText)
    {
        $this->enableRichText = (boolean) $enableRichText;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEnableRichText()
    {
        return (boolean) $this->enableRichText;
    }

    /**
     * @param integer $rows
     * @return Text
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
        return $this;
    }

    /**
     * @return integer
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return string
     */
    public function getRenderType()
    {
        return $this->renderType;
    }

    /**
     * @param string $renderType
     */
    public function setRenderType($renderType)
    {
        $this->renderType = $renderType;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Fetch richtext editor configuration preset
     *
     * The following places are looked at:
     *
     * 1. 'richtextConfiguration' attribute of the current tag
     * 2. PageTSconfig: "RTE.tx_flux.preset"
     * 3. PageTSconfig: "RTE.default.preset"
     *
     * @return string
     */
    public function getRichtextConfiguration()
    {
        //tag attribute
        if (false === empty($this->richtextConfiguration)) {
            return $this->richtextConfiguration;
        }

        $pageTSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($this->getPageId());

        if (true === isset($pageTSconfig['RTE.']['tx_flux.']['preset'])
            && false === empty($pageTSconfig['RTE.']['tx_flux.']['preset'])
        ) {
            return $pageTSconfig['RTE.']['tx_flux.']['preset'];
        }

        return $pageTSconfig['RTE.']['default.']['preset'];
    }

    /**
     * Get the current page ID from the TYPO3 backend
     *
     * @return integer Page UID
     */
    protected function getPageId()
    {
        $formRecord = $this->getFormObject()->getOption('record');
        if (isset($formRecord['pid'])) {
            //form that has already been saved
            return (int) $formRecord['pid'];
        }

        if (is_array($_GET['edit'])) {
            //editing a record
            $type = key($_GET['edit']);
            $id   = key($_GET['edit'][$type]);
            $mode = $_GET['edit'][$type][$id];

            if ($mode === 'new') {
                return $id;
            }
        }

        throw new \Exception('Unable to determine page ID', 1501156095);
    }

    /**
     * Get the current form the element is attached to
     *
     * @return \FluidTYPO3\Flux\Form Form object
     */
    protected function getFormObject()
    {
        $elem = $this;
        while ($elem = $elem->getParent()) {
            if ($elem instanceof \FluidTYPO3\Flux\Form) {
                return $elem;
            }
        }
        return null;
    }

    /**
     * @param string $richtextConfiguration
     * @return Text
     */
    public function setRichtextConfiguration($richtextConfiguration)
    {
        $this->richtextConfiguration = $richtextConfiguration;
        return $this;
    }

    /**
     * Check if the CKEditor extension is active
     *
     * @return bool True if the extension is active
     */
    protected function isCkEditorActive()
    {
        return \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('rte_ckeditor');
    }
}
