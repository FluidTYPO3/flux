<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Configuration\BackendConfigurationManager;
use FluidTYPO3\Flux\Form;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
        GeneralUtility::logDeprecatedFunction();
        $this->defaultExtras = $defaultExtras;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultExtras()
    {
        GeneralUtility::logDeprecatedFunction();
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
        return $this->richtextConfiguration ?: $this->getPageTsConfigForRichTextEditor();
    }

    /**
     * @return string
     */
    protected function getPageTsConfigForRichTextEditor()
    {
        $configurationManager = $this->getObjectManager()->get(ConfigurationManagerInterface::class);
        if ($configurationManager instanceof BackendConfigurationManager) {
            $pageUid = $configurationManager->getCurrentPageId();
        } else {
            $root = $this->getRoot();
            $pageUid = $root instanceof Form ? $root->getOption('record')['pid'] ?? 0 : 0;
        }

        if ($pageUid) {
            return BackendUtility::getPagesTSconfig($pageUid)['RTE.']['default.']['preset'] ?? 'default';
        }
        return 'default';
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
