<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

interface InlineRelationFieldInterface extends RelationFieldInterface
{
    public function setForeignTypes(array $foreignTypes): self;
    public function getForeignTypes(): array;
    public function setCollapseAll(bool $collapseAll): self;
    public function getCollapseAll(): bool;
    public function setEnabledControls(array $enabledControls): self;
    public function getEnabledControls(): array;
    public function setExpandSingle(bool $expandSingle): self;
    public function getExpandSingle(): bool;
    public function setNewRecordLinkAddTitle(bool $newRecordLinkAddTitle): self;
    public function getNewRecordLinkAddTitle(): bool;
    public function setNewRecordLinkPosition(string $newRecordLinkPosition): self;
    public function getNewRecordLinkPosition(): string;
    public function setShowAllLocalizationLink(bool $showAllLocalizationLink): self;
    public function getShowAllLocalizationLink(): bool;
    public function setShowPossibleLocalizationRecords(bool $showPossibleLocalizationRecords): self;
    public function getShowPossibleLocalizationRecords(): bool;
    public function setShowRemovedLocalizationRecords(bool $showRemovedLocalizationRecords): self;
    public function getShowRemovedLocalizationRecords(): bool;
    public function setShowSynchronizationLink(bool $showSynchronizationLink): self;
    public function getShowSynchronizationLink(): bool;
    public function setUseCombination(bool $useCombination): self;
    public function getUseCombination(): bool;
    public function setUseSortable(bool $useSortable): self;
    public function getUseSortable(): bool;
    public function setForeignMatchFields(array $foreignMatchFields): self;
    public function getForeignMatchFields(): array;
}
