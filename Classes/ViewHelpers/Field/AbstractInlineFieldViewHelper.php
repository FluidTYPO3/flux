<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\InlineRelationFieldInterface;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Inline-style FlexForm field ViewHelper
 */
abstract class AbstractInlineFieldViewHelper extends AbstractRelationFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'collapseAll',
            'boolean',
            'If true, all child records are shown as collapsed.',
            false,
            false
        );
        $this->registerArgument(
            'expandSingle',
            'boolean',
            'Show only one expanded record at any time. If a new record is expanded, all others are collapsed.',
            false,
            false
        );
        $this->registerArgument(
            'newRecordLinkAddTitle',
            'boolean',
            "Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')",
            false,
            false
        );
        $this->registerArgument(
            'newRecordLinkPosition',
            'string',
            "Where to show 'Add new' link. Can be 'top', 'bottom', 'both' or 'none'.",
            false,
            'top'
        );
        $this->registerArgument(
            'useCombination',
            'boolean',
            "For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit ' .
            'attributes and the related child record.",
            false,
            false
        );
        $this->registerArgument('useSortable', 'boolean', 'Allow manual sorting of records.', false, false);
        $this->registerArgument(
            'showPossibleLocalizationRecords',
            'boolean',
            'Show unlocalized records which are in the original language, but not yet localized.',
            false,
            false
        );
        $this->registerArgument(
            'showRemovedLocalizationRecords',
            'boolean',
            'Show records which were once localized but do not exist in the original language anymore.',
            false,
            false
        );
        $this->registerArgument(
            'showAllLocalizationLink',
            'boolean',
            "Show the 'localize all records' link to fetch untranslated records from the original language.",
            false,
            false
        );
        $this->registerArgument(
            'showSynchronizationLink',
            'boolean',
            "Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.",
            false,
            false
        );
        $this->registerArgument(
            'enabledControls',
            'array',
            "Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. ' .
            'Set either one to TRUE or FALSE to show or hide it.",
            false,
            false
        );
        $this->registerArgument('headerThumbnail', 'array', 'Associative array with header thumbnail.', false, false);
        $this->registerArgument(
            'foreignMatchFields',
            'array',
            'The fields and values of the child record which have to match. For FAL the field/key is "fieldname" ' .
            'and the value has to be defined.',
            false,
            false
        );
        $this->registerArgument(
            'foreignTypes',
            'array',
            'Overrides the "types" TCA array of the target table with this array (beware! must be specified fully ' .
            'in order to work!). Expects an array of arrays; indexed by type number - each array containing for ' .
            'example a "showitem" CSV list value of field names to be shown when inline-editing the related record.'
        );
        $this->registerArgument('levelLinksPosition', 'string', 'Level links position.');
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return InlineRelationFieldInterface
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        $component = static::getPreparedComponent('Inline', $renderingContext, $arguments);
        return $component;
    }

    /**
     * @param string $type
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return InlineRelationFieldInterface
     */
    protected static function getPreparedComponent($type, RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var InlineRelationFieldInterface $component */
        $component = parent::getPreparedComponent($type, $renderingContext, $arguments);
        $component->setCollapseAll($arguments['collapseAll']);
        $component->setExpandSingle($arguments['expandSingle']);
        $component->setNewRecordLinkAddTitle($arguments['newRecordLinkAddTitle']);
        $component->setNewRecordLinkPosition($arguments['newRecordLinkPosition']);
        $component->setUseCombination($arguments['useCombination']);
        $component->setUseSortable($arguments['useSortable']);
        $component->setShowPossibleLocalizationRecords($arguments['showPossibleLocalizationRecords']);
        $component->setShowRemovedLocalizationRecords($arguments['showRemovedLocalizationRecords']);
        $component->setShowAllLocalizationLink($arguments['showAllLocalizationLink']);
        $component->setShowSynchronizationLink($arguments['showSynchronizationLink']);
        if (true === is_array($arguments['enabledControls'])) {
            $component->setEnabledControls($arguments['enabledControls']);
        }
        if (true === is_array($arguments['headerThumbnail'])) {
            $component->setHeaderThumbnail($arguments['headerThumbnail']);
        }
        if (true === is_array($arguments['foreignMatchFields'])) {
            $component->setForeignMatchFields($arguments['foreignMatchFields']);
        }
        if (true === is_array($arguments['foreignTypes'])) {
            $component->setForeignTypes($arguments['foreignTypes']);
        }
        $component->setLevelLinksPosition($arguments['levelLinksPosition']);
        return $component;
    }
}
