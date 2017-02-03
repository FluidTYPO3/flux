<?php
namespace FluidTYPO3\Flux\Form\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\AbstractFormField;
use FluidTYPO3\Flux\Form\FieldInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Input
 */
class Input extends AbstractFormField implements FieldInterface
{
    const DEFAULT_VALIDATE = 'trim';

    /**
     * @var integer
     */
    protected $size = 32;

    /**
     * @var integer
     */
    protected $maxCharacters;

    /**
     * @var integer
     */
    protected $minimum;

    /**
     * @var integer
     */
    protected $maximum;

    /**
     * @var string
     */
    protected $placeholder;

    /**
     * @var string
     */
    protected $validate = self::DEFAULT_VALIDATE;

    /**
     * @return array
     */
    public function buildConfiguration()
    {
        $minimum = $this->getMinimum();
        $maximum = $this->getMaximum();
        $validate = $this->getValidate();
        $configuration = $this->prepareConfiguration('input');
        $configuration['placeholder'] = $this->getPlaceholder();
        $configuration['size'] = $this->getSize();
        $configuration['max'] = $this->getMaxCharacters();
        $configuration['eval'] = $validate;
        if (null !== $minimum && null !== $maximum && in_array('int', GeneralUtility::trimExplode(',', $validate))) {
            $configuration['range'] = [
                'lower' => $minimum,
                'upper' => $maximum
            ];
        }
        return $configuration;
    }

    /**
     * @param integer $maxCharacters
     * @return Input
     */
    public function setMaxCharacters($maxCharacters)
    {
        $this->maxCharacters = $maxCharacters;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxCharacters()
    {
        return $this->maxCharacters;
    }

    /**
     * @param integer $maximum
     * @return Input
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * @param integer $minimum
     * @return Input
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
        return $this;
    }

    /**
     * @return integer
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * @param string $placeholder
     * @return Input
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
     * @param integer $size
     * @return Input
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }
}
