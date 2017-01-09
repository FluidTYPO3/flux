<?php
namespace FluidTYPO3\Flux\ViewHelpers\Wizard;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Wizard\Suggest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Field Wizard: Suggest
 *
 * See https://docs.typo3.org/typo3cms/TCAReference/AdditionalFeatures/CoreWizardScripts/Index.html
 * for details about the behaviors that are controlled by arguments.
 */
class SuggestViewHelper extends AbstractWizardViewHelper
{

    /**
     * Initialize arguments
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument(
            'table',
            'string',
            'Table to search. If left out will use the table defined by the parent field'
        );
        $this->registerArgument('pidList', 'string', 'List of storage page UIDs', false, '0');
        $this->registerArgument('pidDepth', 'integer', 'Depth of recursive storage page UID lookups', false, 99);
        $this->registerArgument(
            'minimumCharacters',
            'integer',
            'Minimum number of characters that must be typed before search begins',
            false,
            1
        );
        $this->registerArgument(
            'maxPathTitleLength',
            'integer',
            'Maximum path segment length - crops titles over this length',
            false,
            15
        );
        $this->registerArgument(
            'searchWholePhrase',
            'boolean',
            'A match requires a full word that matches the search value',
            false,
            false
        );
        $this->registerArgument(
            'searchCondition',
            'string',
            'Search condition - for example, if table is pages "doktype = 1" to only allow standard pages',
            false,
            ''
        );
        $this->registerArgument('cssClass', 'string', 'Use this CSS class for all list items', false, '');
        $this->registerArgument(
            'receiverClass',
            'string',
            'Class reference, target class should be derived from "t3lib_tceforms_suggest_defaultreceiver"',
            false,
            ''
        );
        $this->registerArgument(
            'renderFunc',
            'string',
            'Reference to function which processes all records displayed in results',
            false,
            ''
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Suggest
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        /** @var Suggest $component */
        $component = static::getPreparedComponent('Suggest', $renderingContext, $arguments);
        $component->setTable($arguments['table']);
        $component->setStoragePageUids($arguments['pidList']);
        $component->setStoragePageRecursiveDepth($arguments['pidDepth']);
        $component->setMinimumCharacters($arguments['minimumCharacters']);
        $component->setMaxPathTitleLength($arguments['maxPathTitleLength']);
        $component->setSearchWholePhrase($arguments['setSearchWholePhrase']);
        $component->setSearchCondition($arguments['searchCondition']);
        $component->setCssClass($arguments['cssClass']);
        $component->setReceiverClass($arguments['receiverClass']);
        $component->setRenderFunction($arguments['renderFunc']);
        return $component;
    }
}
