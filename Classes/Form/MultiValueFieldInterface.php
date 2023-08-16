<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface MultiValueFieldInterface extends FieldInterface
{
    public function setSize(int $size): self;
    public function getSize(): int;
    public function setMultiple(bool $multiple): self;
    public function getMultiple(): bool;
    public function setMaxItems(int $maxItems): self;
    public function getMaxItems(): ?int;
    public function setMinItems(int $minItems): self;
    public function getMinItems(): int;
    public function setItemListStyle(?string $itemListStyle): self;
    public function getItemListStyle(): ?string;
    public function setSelectedListStyle(?string $selectedListStyle): self;
    public function getSelectedListStyle(): ?string;

    /**
     * @param boolean|string $emptyOption
     */
    public function setEmptyOption($emptyOption): self;

    /**
     * @return boolean|string
     */
    public function getEmptyOption();
}
