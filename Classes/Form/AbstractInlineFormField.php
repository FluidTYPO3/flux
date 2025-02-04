<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\InlineFieldControls;
use FluidTYPO3\Flux\Enum\InlineFieldNewRecordButtonPosition;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

abstract class AbstractInlineFormField extends AbstractRelationFormField implements InlineRelationFieldInterface
{
    /**
     * If true, all child records are shown as collapsed.
     */
    protected bool $collapseAll = false;

    /**
     * Show only one expanded record at any time. If a new record is expanded,
     * all others are collapsed.
     */
    protected bool $expandSingle = false;

    /**
     * Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')
     */
    protected bool $newRecordLinkAddTitle = false;

    /**
     * Record link position - can be either \FluidTYPO3\Flux\Form::POSITION_TOP,
     * \FluidTYPO3\Flux\Form::POSITION_BOTTOM, \FluidTYPO3\Flux\Form::POSITION_BOTH or
     * \FluidTYPO3\Flux\Form::POSITION_NONE.
     */
    protected string $newRecordLinkPosition = InlineFieldNewRecordButtonPosition::TOP;

    /**
     * For use on bidirectional relations using an intermediary table.
     * In combinations, it's possible to edit attributes and the related child record.
     */
    protected bool $useCombination = false;

    /**
     * Allow manual sorting of child objects.
     */
    protected bool $useSortable = false;

    /**
     * Show unlocalized records which are in the original language, but not yet localized.
     */
    protected bool $showPossibleLocalizationRecords = false;

    /**
     * Show records which were once localized but do not exist in the original
     * language anymore.
     */
    protected bool $showRemovedLocalizationRecords = false;

    /**
     * Defines whether to show the 'localize all records' link to fetch untranslated
     * records from the original language.
     */
    protected bool $showAllLocalizationLink = false;

    /**
     * Defines whether to show a 'synchronize' link to update to a 1:1 translation with
     * the original language.
     */
    protected bool $showSynchronizationLink = false;

    /**
     * Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete'
     * and 'localize'. Set either one to TRUE or FALSE to show or hide it.
     */
    protected array $enabledControls = [
        InlineFieldControls::INFO => false,
        InlineFieldControls::NEW => true,
        InlineFieldControls::DRAGDROP => true,
        InlineFieldControls::SORT => true,
        InlineFieldControls::HIDE => true,
        InlineFieldControls::DELETE => false,
        InlineFieldControls::LOCALIZE => false,
    ];

    /**
     * Array of field=>value pairs which are always used in conditions as well as inserted into new
     * records created through this form component.
     */
    protected array $foreignMatchFields = [];
    protected ?array $headerThumbnail = null;
    protected ?string $levelLinksPosition = null;
    protected array $overrideChildTca = [];
    protected array $foreignTypes = [];

    public function prepareConfiguration(string $type): array
    {
        $configuration = parent::prepareConfiguration($type);
        $configuration['foreign_match_fields'] = $this->getForeignMatchFields();
        $configuration['overrideChildTca'] = $this->getOverrideChildTca();
        $configuration['foreign_types'] = $this->getForeignTypes();
        $configuration['appearance'] = [
            'collapseAll' => $this->getCollapseAll(),
            'expandSingle' => $this->getExpandSingle(),
            'newRecordLinkAddTitle' => $this->getNewRecordLinkAddTitle(),
            'newRecordLinkPosition' => $this->getNewRecordLinkPosition(),
            'useCombination' => $this->getUseCombination(),
            'useSortable' => $this->getUseSortable(),
            'showPossibleLocalizationRecords' => $this->getShowPossibleLocalizationRecords(),
            'showRemovedLocalizationRecords' => $this->getShowRemovedLocalizationRecords(),
            'showAllLocalizationLink' => $this->getShowAllLocalizationLink(),
            'showSynchronizationLink' => $this->getShowSynchronizationLink(),
            'enabledControls' => $this->getEnabledControls(),
            'headerThumbnail' => $this->getHeaderThumbnail(),
            'levelLinksPosition' => $this->getLevelLinksPosition(),
        ];
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0', '>=')) {
            unset($configuration['appearance']['showRemovedLocalizationRecords']);
        }

        $configuration['behaviour'] = [
            'localizationMode' => $this->getLocalizationMode(),
            'disableMovingChildrenWithParent' => $this->getDisableMovingChildrenWithParent(),
        ];
        return $configuration;
    }

    public function setCollapseAll(bool $collapseAll): self
    {
        $this->collapseAll = $collapseAll;
        return $this;
    }

    public function getCollapseAll(): bool
    {
        return $this->collapseAll;
    }

    public function setEnabledControls(array $enabledControls): self
    {
        $this->enabledControls = $enabledControls;
        return $this;
    }

    public function getEnabledControls(): array
    {
        return $this->enabledControls;
    }

    public function setExpandSingle(bool $expandSingle): self
    {
        $this->expandSingle = $expandSingle;
        return $this;
    }

    public function getExpandSingle(): bool
    {
        return $this->expandSingle;
    }

    public function setNewRecordLinkAddTitle(bool $newRecordLinkAddTitle): self
    {
        $this->newRecordLinkAddTitle = $newRecordLinkAddTitle;
        return $this;
    }

    public function getNewRecordLinkAddTitle(): bool
    {
        return $this->newRecordLinkAddTitle;
    }

    public function setNewRecordLinkPosition(string $newRecordLinkPosition): self
    {
        $this->newRecordLinkPosition = $newRecordLinkPosition;
        return $this;
    }

    public function getNewRecordLinkPosition(): string
    {
        return $this->newRecordLinkPosition;
    }

    public function setShowAllLocalizationLink(bool $showAllLocalizationLink): self
    {
        $this->showAllLocalizationLink = $showAllLocalizationLink;
        return $this;
    }

    public function getShowAllLocalizationLink(): bool
    {
        return $this->showAllLocalizationLink;
    }

    public function setShowPossibleLocalizationRecords(bool $showPossibleLocalizationRecords): self
    {
        $this->showPossibleLocalizationRecords = $showPossibleLocalizationRecords;
        return $this;
    }

    public function getShowPossibleLocalizationRecords(): bool
    {
        return $this->showPossibleLocalizationRecords;
    }

    public function setShowRemovedLocalizationRecords(bool $showRemovedLocalizationRecords): self
    {
        $this->showRemovedLocalizationRecords = $showRemovedLocalizationRecords;
        return $this;
    }

    public function getShowRemovedLocalizationRecords(): bool
    {
        return $this->showRemovedLocalizationRecords;
    }

    public function setShowSynchronizationLink(bool $showSynchronizationLink): self
    {
        $this->showSynchronizationLink = $showSynchronizationLink;
        return $this;
    }

    public function getShowSynchronizationLink(): bool
    {
        return $this->showSynchronizationLink;
    }

    public function setUseCombination(bool $useCombination): self
    {
        $this->useCombination = $useCombination;
        return $this;
    }

    public function getUseCombination(): bool
    {
        return $this->useCombination;
    }

    public function setUseSortable(bool $useSortable): self
    {
        $this->useSortable = $useSortable;
        return $this;
    }

    public function getUseSortable(): bool
    {
        return $this->useSortable;
    }

    public function setForeignMatchFields(array $foreignMatchFields): self
    {
        $this->foreignMatchFields = $foreignMatchFields;
        return $this;
    }

    public function getForeignMatchFields(): array
    {
        return $this->foreignMatchFields;
    }

    public function setHeaderThumbnail(array $headerThumbnail): self
    {
        $this->headerThumbnail = $headerThumbnail;
        return $this;
    }

    public function getHeaderThumbnail(): ?array
    {
        return $this->headerThumbnail;
    }

    public function setLevelLinksPosition(?string $levelLinksPosition): self
    {
        $this->levelLinksPosition = $levelLinksPosition;
        return $this;
    }

    public function getLevelLinksPosition(): ?string
    {
        return $this->levelLinksPosition;
    }

    public function setOverrideChildTca(array $overrideChildTca): self
    {
        $this->overrideChildTca = $overrideChildTca;
        return $this;
    }

    public function getOverrideChildTca():array
    {
        return $this->overrideChildTca;
    }

    public function setForeignTypes(array $foreignTypes): self
    {
        $this->foreignTypes = $foreignTypes;
        return $this;
    }

    public function getForeignTypes(): array
    {
        return $this->foreignTypes;
    }
}
