<?php
namespace FluidTYPO3\Flux\Form;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form;

/**
 * AbstractInlineFormField
 */
abstract class AbstractInlineFormField extends AbstractRelationFormField implements InlineRelationFieldInterface
{

    /**
     * If true, all child records are shown as collapsed.
     *
     * @var boolean
     */
    protected $collapseAll = false;

    /**
     * Show only one expanded record at any time. If a new record is expanded,
     * all others are collapsed.
     *
     * @var boolean
     */
    protected $expandSingle = false;

    /**
     * Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')
     *
     * @var boolean
     */
    protected $newRecordLinkAddTitle = false;

    /**
     * Record link position - can be either \FluidTYPO3\Flux\Form::POSITION_TOP,
     * \FluidTYPO3\Flux\Form::POSITION_BOTTOM, \FluidTYPO3\Flux\Form::POSITION_BOTH or
     * \FluidTYPO3\Flux\Form::POSITION_NONE.
     *
     * @var string
     */
    protected $newRecordLinkPosition = Form::POSITION_TOP;

    /**
     * For use on bidirectional relations using an intermediary table.
     * In combinations, it's possible to edit attributes and the related child record.
     *
     * @var boolean
     */
    protected $useCombination = false;

    /**
     * Allow manual sorting of child objects.
     *
     * @var boolean
     */
    protected $useSortable = false;

    /**
     * Show unlocalized records which are in the original language, but not yet localized.
     *
     * @var boolean
     */
    protected $showPossibleLocalizationRecords = false;

    /**
     * Show records which were once localized but do not exist in the original
     * language anymore.
     *
     * @var boolean
     */
    protected $showRemovedLocalizationRecords = false;

    /**
     * Defines whether to show the 'localize all records' link to fetch untranslated
     * records from the original language.
     *
     * @var boolean
     */
    protected $showAllLocalizationLink = false;

    /**
     * Defines whether to show a 'synchronize' link to update to a 1:1 translation with
     * the original language.
     *
     * @var boolean
     */
    protected $showSynchronizationLink = false;

    /**
     * Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete'
     * and 'localize'. Set either one to TRUE or FALSE to show or hide it.
     *
     * @var array
     */
    protected $enabledControls = [
        Form::CONTROL_INFO => false,
        Form::CONTROL_NEW => true,
        Form::CONTROL_DRAGDROP => true,
        Form::CONTROL_SORT => true,
        Form::CONTROL_HIDE => true,
        Form::CONTROL_DELETE => false,
        Form::CONTROL_LOCALISE => false,
    ];

    /**
     * Array of field=>value pairs which are always used in conditions as well as inserted into new
     * records created through this form component.
     *
     * @var array
     */
    protected $foreignMatchFields = [];

    /**
     * @var array
     */
    protected $headerThumbnail = null;

    /**
     * @var string
     */
    protected $levelLinksPosition = null;

    /**
     * @var string
     */
    protected $foreignSelectorFieldTcaOverride;

    /**
     * @var array
     */
    protected $foreignTypes = null;

    /**
     * @param string $type
     * @return array
     */
    public function prepareConfiguration($type)
    {
        $configuration = parent::prepareConfiguration($type);
        $configuration['foreign_match_fields'] = $this->getForeignMatchFields();
        $configuration['foreign_selector_fieldTcaOverride'] = $this->getForeignSelectorFieldTcaOverride();
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
        $configuration['behaviour'] = [
            'localizationMode' => $this->getLocalizationMode(),
            'localizeChildrenAtParentLocalization' => $this->getLocalizeChildrenAtParentLocalization(),
            'disableMovingChildrenWithParent' => $this->getDisableMovingChildrenWithParent(),
        ];
        return $configuration;
    }

    /**
     * @param boolean $collapseAll
     * @return AbstractInlineFormField
     */
    public function setCollapseAll($collapseAll)
    {
        $this->collapseAll = $collapseAll;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCollapseAll()
    {
        return $this->collapseAll;
    }

    /**
     * @param array $enabledControls
     * @return AbstractInlineFormField
     */
    public function setEnabledControls(array $enabledControls)
    {
        $this->enabledControls = $enabledControls;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnabledControls()
    {
        return $this->enabledControls;
    }

    /**
     * @param boolean $expandSingle
     * @return AbstractInlineFormField
     */
    public function setExpandSingle($expandSingle)
    {
        $this->expandSingle = $expandSingle;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getExpandSingle()
    {
        return $this->expandSingle;
    }

    /**
     * @param boolean $newRecordLinkAddTitle
     * @return AbstractInlineFormField
     */
    public function setNewRecordLinkAddTitle($newRecordLinkAddTitle)
    {
        $this->newRecordLinkAddTitle = $newRecordLinkAddTitle;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getNewRecordLinkAddTitle()
    {
        return $this->newRecordLinkAddTitle;
    }

    /**
     * @param string $newRecordLinkPosition
     * @return AbstractInlineFormField
     */
    public function setNewRecordLinkPosition($newRecordLinkPosition)
    {
        $this->newRecordLinkPosition = $newRecordLinkPosition;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewRecordLinkPosition()
    {
        return $this->newRecordLinkPosition;
    }

    /**
     * @param boolean $showAllLocalizationLink
     * @return AbstractInlineFormField
     */
    public function setShowAllLocalizationLink($showAllLocalizationLink)
    {
        $this->showAllLocalizationLink = $showAllLocalizationLink;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowAllLocalizationLink()
    {
        return $this->showAllLocalizationLink;
    }

    /**
     * @param boolean $showPossibleLocalizationRecords
     * @return AbstractInlineFormField
     */
    public function setShowPossibleLocalizationRecords($showPossibleLocalizationRecords)
    {
        $this->showPossibleLocalizationRecords = $showPossibleLocalizationRecords;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowPossibleLocalizationRecords()
    {
        return $this->showPossibleLocalizationRecords;
    }

    /**
     * @param boolean $showRemovedLocalizationRecords
     * @return AbstractInlineFormField
     */
    public function setShowRemovedLocalizationRecords($showRemovedLocalizationRecords)
    {
        $this->showRemovedLocalizationRecords = $showRemovedLocalizationRecords;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowRemovedLocalizationRecords()
    {
        return $this->showRemovedLocalizationRecords;
    }

    /**
     * @param boolean $showSynchronizationLink
     * @return AbstractInlineFormField
     */
    public function setShowSynchronizationLink($showSynchronizationLink)
    {
        $this->showSynchronizationLink = $showSynchronizationLink;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getShowSynchronizationLink()
    {
        return $this->showSynchronizationLink;
    }

    /**
     * @param boolean $useCombination
     * @return AbstractInlineFormField
     */
    public function setUseCombination($useCombination)
    {
        $this->useCombination = $useCombination;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseCombination()
    {
        return $this->useCombination;
    }

    /**
     * @param boolean $useSortable
     * @return AbstractInlineFormField
     */
    public function setUseSortable($useSortable)
    {
        $this->useSortable = $useSortable;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getUseSortable()
    {
        return $this->useSortable;
    }

    /**
     * @param array $foreignMatchFields
     * @return AbstractInlineFormField
     */
    public function setForeignMatchFields(array $foreignMatchFields)
    {
        $this->foreignMatchFields = $foreignMatchFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getForeignMatchFields()
    {
        return $this->foreignMatchFields;
    }

    /**
     * @param array $headerThumbnail
     * @return AbstractInlineFormField
     */
    public function setHeaderThumbnail(array $headerThumbnail)
    {
        $this->headerThumbnail = $headerThumbnail;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderThumbnail()
    {
        return $this->headerThumbnail;
    }

    /**
     * @param string $levelLinksPosition
     * @return AbstractInlineFormField
     */
    public function setLevelLinksPosition($levelLinksPosition)
    {
        $this->levelLinksPosition = $levelLinksPosition;
        return $this;
    }

    /**
     * @return array
     */
    public function getLevelLinksPosition()
    {
        return $this->levelLinksPosition;
    }

    /**
     * @param string $foreignSelectorFieldTcaOverride
     * @return RelationFieldInterface
     */
    public function setForeignSelectorFieldTcaOverride($foreignSelectorFieldTcaOverride)
    {
        $this->foreignSelectorFieldTcaOverride = $foreignSelectorFieldTcaOverride;
        return $this;
    }

    /**
     * @return string
     */
    public function getForeignSelectorFieldTcaOverride()
    {
        return $this->foreignSelectorFieldTcaOverride;
    }

    /**
     * @param array $foreignTypes
     * @return RelationFieldInterface
     */
    public function setForeignTypes($foreignTypes)
    {
        $this->foreignTypes = true === is_array($foreignTypes) ? $foreignTypes : null;
        return $this;
    }

    /**
     * @return array
     */
    public function getForeignTypes()
    {
        return $this->foreignTypes;
    }
}
