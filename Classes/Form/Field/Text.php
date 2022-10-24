<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;

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
     * @var string
     */
    protected $placeholder;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $configuration = $this->prepareConfiguration('text');
        $configuration['rows'] = $this->getRows();
        $configuration['cols'] = $this->getColumns();
        $configuration['eval'] = $this->getValidate();
        $configuration['placeholder'] = $this->getPlaceholder();
        if (true === $this->getEnableRichText()) {
            $configuration['enableRichtext'] = true;
            $configuration['richtextConfiguration'] = $this->getRichtextConfiguration();
        }
        $renderType = $this->getRenderType();
        if (false === empty($renderType)) {
            $configuration['renderType'] = $renderType;
            $configuration['format'] = $this->getFormat();
        }
        return $configuration;
    }

    /**
     * @param integer $columns
     * @return $this
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
     * @deprecated Will be removed in next major version
     * @param string $defaultExtras
     * @return $this
     */
    public function setDefaultExtras($defaultExtras)
    {
        $this->defaultExtras = $defaultExtras;
        return $this;
    }

    /**
     * @deprecated Will be removed in next major version
     * @return string
     */
    public function getDefaultExtras()
    {
        return $this->defaultExtras;
    }

    /**
     * @param boolean $enableRichText
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setRenderType($renderType)
    {
        $this->renderType = $renderType;
        return $this;
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
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param string $placeholder
     * @return Text
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
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
        return $this->richtextConfiguration ?: $this->getPageTsConfigForRichTextEditor();
    }

    /**
     * @return string
     */
    protected function getPageTsConfigForRichTextEditor()
    {
        $pageUid = 0;
        $root = $this->getRoot();
        if ($root instanceof Form) {
            /** @var array|null $record */
            $record = $root->getOption('record');
            if ($record !== null) {
                $pageUid = (integer) $record['pid'];
            }
        }

        return BackendUtility::getPagesTSconfig($pageUid)['RTE.']['default.']['preset'] ?? 'default';
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
}
