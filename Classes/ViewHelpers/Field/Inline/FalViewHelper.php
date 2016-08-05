<?php
namespace FluidTYPO3\Flux\ViewHelpers\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Form\Field\Inline\Fal;
use FluidTYPO3\Flux\ViewHelpers\Field\AbstractInlineFieldViewHelper;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Creates a FAL IRRE field
 *
 * To get the file references, assigned with that field in a flux form, you will have to use EXT:vhs but there are
 * two different ViewHelpers for fluidpages templates and fluidcontent elements.
 *
 * Example how to get the first file reference from a fluidcontent element, for the flux field named "settings.files":
 *
 *     {v:content.resources.fal(field: 'settings.files')
 *         -> v:iterator.first()
 *         -> v:variable.set(name: 'settings.files')}
 *
 * And now the example how to get the first file reference from a fluidpages template, for the flux field
 * named "settings.files":
 *
 *     {v:page.resources.fal(field: 'settings.files')
 *         -> v:iterator.first()
 *         -> v:variable.set(name: 'settings.files')}
 *
 * ### Usage warning
 *
 * Due to [TYPO3 core bug #71239](https://forge.typo3.org/issues/71239), using
 * FAL references within sections (`<flux:form.section>`) in content elements
 * or within the page configuration does not work.
 *
 * When choosing a file in one section element, you will see it in all sections.
 * When choosing a file in a page configuration, it will be visible in the subpages
 * configuration, too.
 *
 * This issue will most likely not be fixed before TYPO3 8, so do not use it.
 *
 * Alternatively, you could use `<flux:field.file>`.
 *
 * ### Selecting and rendering an image
 *
 * #### Selecting a single image
 *
 *     <flux:field.inline.fal name="settings.image" required="1" maxItems="1" minItems="1"/>
 *
 * #### Rendering the image
 *
 *     {v:content.resources.fal(field: 'settings.image') -> v:iterator.first() -> v:variable.set(name: 'image')}
 *     <f:image treatIdAsReference="1" src="{image.id}" title="{image.title}" alt="{image.alternative}"/><br/>
 *
 * #### Rendering multiple images
 *
 *     <f:for each="{v:content.resources.fal(field: 'settings.image')}" as="image">
 *         <f:image treatIdAsReference="1" src="{image.id}" title="{image.title}" alt="{image.alternative}"/><br/>
 *     </f:for>
 */
class FalViewHelper extends AbstractInlineFieldViewHelper
{

    /**
     * Initialize
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->overrideArgument(
            'table',
            'string',
            'Define foreign table name to turn selector into a record selector for that table',
            false,
            Fal::DEFAULT_TABLE
        );
        $this->overrideArgument(
            'foreignField',
            'string',
            'The foreign_field is the field of the child record pointing to the parent record. This defines where to ' .
            'store the uid of the parent record.',
            false,
            Fal::DEFAULT_FOREIGN_FIELD
        );
        $this->overrideArgument(
            'foreignLabel',
            'string',
            "If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.",
            false,
            Fal::DEFAULT_FOREIGN_LABEL
        );
        $this->overrideArgument(
            'foreignSelector',
            'string',
            'A selector is used to show all possible child records that could be used to create a relation with the ' .
            'parent record. It will be rendered as a multi-select-box. On clicking on an item inside the ' .
            'selector a new relation is created. The foreign_selector points to a field of the foreign_table that ' .
            'is responsible for providing a selector-box – this field on the foreign_table usually has the type ' .
            '"select" and also has a "foreign_table" defined.',
            false,
            Fal::DEFAULT_FOREIGN_SELECTOR
        );
        $this->overrideArgument(
            'foreignSortby',
            'string',
            'Field on the child record (or on the intermediate table) that stores the manual sorting information.',
            false,
            Fal::DEFAULT_FOREIGN_SORTBY
        );
        $this->overrideArgument(
            'foreignTableField',
            'string',
            'The field of the child record pointing to the parent record. This defines where to store the table ' .
            'name of the parent record. On setting this configuration key together with foreign_field, the child ' .
            'record knows what its parent record is - so the child record could also be used on other parent tables.',
            false,
            Fal::DEFAULT_FOREIGN_TABLE_FIELD
        );
        $this->overrideArgument(
            'localizationMode',
            'string',
            "Set whether children can be localizable ('select') or just inherit from default language ('keep').",
            false,
            Fal::DEFAULT_LOCALIZATION_MODE
        );
        $this->overrideArgument(
            'localizeChildrenAtParentLocalization',
            'boolean',
            'Defines whether children should be localized when the localization of the parent gets created.',
            false,
            Fal::DEFAULT_LOCALIZE_CHILDREN_AT_PARENT_LOCALIZATION
        );

        $this->overrideArgument(
            'newRecordLinkAddTitle',
            'boolean',
            "Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')",
            false,
            Fal::DEFAULT_NEW_RECORD_LINK_ADD_TITLE
        );
        $this->overrideArgument(
            'useSortable',
            'boolean',
            'Allow manual sorting of records.',
            false,
            Fal::DEFAULT_USE_SORTABLE
        );
        $this->overrideArgument(
            'levelLinksPosition',
            'string',
            'Level links position.',
            false,
            Fal::DEFAULT_LEVEL_LINKS_POSITION
        );

        $this->registerArgument(
            'allowedExtensions',
            'string',
            'Allowed File Extensions .',
            false,
            $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
        );
        $this->registerArgument('disallowedExtensions', 'string', 'Disallowed File Extensions .', false, '');
        $this->registerArgument(
            'createNewRelationLinkTitle',
            'string',
            'Override label of "Create new relation" button.',
            false,
            Fal::DEFAULT_CREATE_NEW_RELATION_LINK_TITLE
        );
    }

    /**
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return Fal
     */
    public static function getComponent(RenderingContextInterface $renderingContext, array $arguments)
    {
        $allowedExtensions = $arguments['allowedExtensions'];
        $disallowedExtensions = $arguments['disallowedExtensions'];
        $createNewRelationLinkTitle = $arguments['createNewRelationLinkTitle'];

        /** @var Fal $component */
        $component = static::getPreparedComponent('Inline/Fal', $renderingContext, $arguments);
        if (false === is_array($arguments['foreignMatchFields'])) {
            $component->setForeignMatchFields([
                'fieldname' => $arguments['name']
            ]);
        }
        $component->setForeignSelectorFieldTcaOverride([
            'config' => [
                'appearance' => [
                    'elementBrowserType' => 'file',
                    'elementBrowserAllowed' => $allowedExtensions
                ]
            ]
        ]);
        $component->setFilter([[
                'userFunc' => FileExtensionFilter::class . '->filterInlineChildren',
                'parameters' => [
                    'allowedFileExtensions' => $allowedExtensions,
                    'disallowedFileExtensions' => $disallowedExtensions
                ]
            ]]);

        if (false === isset($arguments['foreignTypes'])) {
            $component->setForeignTypes([
                '0' => [
                    'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.' .
                        'imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
                ],
                File::FILETYPE_IMAGE => [
                    'showitem' => '--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.' .
                        'imageoverlayPalette;imageoverlayPalette,--palette--;;filePalette'
                ],
            ]);
        }

        $component->setCreateNewRelationLinkTitle($createNewRelationLinkTitle);

        return $component;
    }
}
