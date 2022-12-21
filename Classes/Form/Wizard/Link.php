<?php
declare(strict_types=1);
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
 * Link wizard
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by properties.
 *
 * @deprecated Will be removed in Flux 10.0
 */
class Link extends AbstractWizard
{
    protected ?string $name = 'link';
    protected ?string $type = 'popup';
    protected ?string $icon = 'link_popup.gif';

    /**
     * @var string
     */
    protected $activeTab = 'file';

    /**
     * @var integer
     */
    protected $height = 500;

    /**
     * @var integer
     */
    protected $width = 400;

    /**
     * @var string|null|array|\Traversable
     */
    protected $blindLinkOptions = '';

    /**
     * @var string|null|array|\Traversable
     */
    protected $blindLinkFields = '';

    /**
     * @var string|null|array|\Traversable
     */
    protected $allowedExtensions;

    public function buildConfiguration(): array
    {
        $structure = [
            'JSopenParams' => sprintf(
                'height=%d,width=%d,status=0,menubar=0,scrollbars=1',
                $this->getHeight(),
                $this->getWidth()
            ),
            'params' => [
                'blindLinkOptions' => implode(',', $this->getBlindLinkOptions()),
                'blindLinkFields' => implode(',', $this->getBlindLinkFields()),
                'allowedExtensions' => implode(',', $this->getAllowedExtensions())
            ],
            'module' => [
                'name' => 'wizard_element_browser',
                'urlParameters' => [
                    'mode' => 'wizard',
                    'act' => $this->getActiveTab()
                ]
            ]
        ];

        return $structure;
    }

    /**
     * @param string $activeTab
     * @return Link
     */
    public function setActiveTab($activeTab)
    {
        $this->activeTab = $activeTab;
        return $this;
    }

    /**
     * @return string
     */
    public function getActiveTab()
    {
        return $this->activeTab;
    }

    /**
     * @param integer $height
     * @return Link
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
     * @return Link
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
     * @param string|null|array|\Traversable $blindLinkOptions
     * @return Link
     */
    public function setBlindLinkOptions($blindLinkOptions)
    {
        $this->blindLinkOptions = $blindLinkOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlindLinkOptions()
    {
        return $this->convertValueToArray($this->blindLinkOptions);
    }

    /**
     * @param string|null|array|\Traversable $blindLinkFields
     * @return Link
     */
    public function setBlindLinkFields($blindLinkFields)
    {
        $this->blindLinkFields = $blindLinkFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getBlindLinkFields()
    {
        return $this->convertValueToArray($this->blindLinkFields);
    }

    /**
     * @param string|null|array|\Traversable $allowedExtensions
     * @return Link
     */
    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->convertValueToArray($this->allowedExtensions);
    }

    /**
     * @param string|null|array|\Traversable $value
     * @return array
     */
    private function convertValueToArray($value): array
    {
        if ($value === null) {
            return [];
        }
        if (is_scalar($value)) {
            return GeneralUtility::trimExplode(',', (string) $value);
        }
        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        return $value;
    }
}
