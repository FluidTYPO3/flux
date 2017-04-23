<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * MultiValueFieldInterface
 */
interface MultiValueFieldInterface extends FieldInterface
{

    /**
     * @param integer $size
     * @return MultiValueFieldInterface
     */
    public function setSize($size);

    /**
     * @return integer
     */
    public function getSize();

    /**
     * @param boolean $multiple
     */
    public function setMultiple($multiple);

    /**
     * @return boolean
     */
    public function getMultiple();

    /**
     * @param integer $maxItems
     * @return MultiValueFieldInterface
     */
    public function setMaxItems($maxItems);

    /**
     * @return integer
     */
    public function getMaxItems();

    /**
     * @param integer $minItems
     * @return MultiValueFieldInterface
     */
    public function setMinItems($minItems);

    /**
     * @return integer
     */
    public function getMinItems();

    /**
     * @param string $itemListStyle
     * @return MultiValueFieldInterface
     */
    public function setItemListStyle($itemListStyle);

    /**
     * @return string
     */
    public function getItemListStyle();

    /**
     * @param string $selectedListStyle
     * @return MultiValueFieldInterface
     */
    public function setSelectedListStyle($selectedListStyle);

    /**
     * @return string
     */
    public function getSelectedListStyle();
}
