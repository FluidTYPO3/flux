<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface RelationFieldInterface extends MultiValueFieldInterface
{
    public function setCondition(?string $condition): self;
    public function getCondition(): ?string;
    public function setForeignField(string $foreignField): self;
    public function getForeignField(): ?string;
    public function setManyToMany(?string $manyToMany): self;
    public function getManyToMany(): ?string;
    public function getMatchFields(): array;
    public function setMatchFields(array $matchFields): self;
    public function setTable(string $table): self;
    public function getTable(): string;
    public function setDisableMovingChildrenWithParent(bool $disableMovingChildrenWithParent): self;
    public function getDisableMovingChildrenWithParent(): bool;
    public function setForeignDefaultSortby(string $foreignDefaultSortby): self;
    public function getForeignDefaultSortby(): ?string;
    public function setForeignLabel(?string $foreignLabel): self;
    public function getForeignLabel(): ?string;
    public function setForeignSelector(?string $foreignSelector): self;
    public function getForeignSelector(): ?string;
    public function setForeignSortby(?string $foreignSortby): self;
    public function getForeignSortby(): ?string;
    public function setForeignTableField(?string $foreignTableField): self;
    public function getForeignTableField(): ?string;
    public function setForeignUnique(?string $foreignUnique): self;
    public function getForeignUnique(): ?string;
    public function setLocalizationMode(?string $localizationMode): self;
    public function getLocalizationMode(): ?string;
    public function setSymmetricField(?string $symmetricField): self;
    public function getSymmetricField(): ?string;
    public function setSymmetricLabel(?string $symmetricLabel): self;
    public function getSymmetricLabel(): ?string;
    public function setSymmetricSortby(?string $symmetricSortby): self;
    public function getSymmetricSortby(): ?string;
    public function setShowThumbnails(bool $showThumbnails): self;
    public function getShowThumbnails(): bool;
    public function setOppositeField(?string $oppositeField): self;
    public function getOppositeField(): ?string;
}
