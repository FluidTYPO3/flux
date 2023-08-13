<?php
declare(strict_types=1);
namespace FluidTYPO3\Flux\Form\Field\Inline;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use FluidTYPO3\Flux\Enum\InlineFieldControls;
use FluidTYPO3\Flux\Form\AbstractInlineFormField;

class Fal extends AbstractInlineFormField
{
    const DEFAULT_TABLE = 'sys_file_reference';
    const DEFAULT_FOREIGN_FIELD = 'uid_foreign';
    const DEFAULT_FOREIGN_TABLE_FIELD = 'tablenames';
    const DEFAULT_FOREIGN_LABEL = 'uid_local';
    const DEFAULT_FOREIGN_SELECTOR = 'uid_local';
    const DEFAULT_FOREIGN_SORTBY = 'sorting_foreign';
    const DEFAULT_USE_SORTABLE = true;
    const DEFAULT_LEVEL_LINKS_POSITION = 'both';
    const DEFAULT_LOCALIZATION_MODE = 'select';
    const DEFAULT_NEW_RECORD_LINK_ADD_TITLE = true;
    const DEFAULT_CREATE_NEW_RELATION_LINK_TITLE = 'LLL:EXT:lang/locallang_core.xlf:cm.createNewRelation';

    /**
     * @var string
     */
    protected $table = self::DEFAULT_TABLE;

    /**
     * The foreign_field is the field of the child record pointing to the
     * parent record. This defines where to store the uid of the parent record.
     *
     * @var string
     */
    protected $foreignField = self::DEFAULT_FOREIGN_FIELD;

    /**
     * The field of the child record pointing to the parent record. This defines
     * where to store the table name of the parent record. On setting this
     * configuration key together with foreign_field, the child record knows what
     * its parent record is – so the child record could also be used on other
     * parent tables.
     *
     * @var string
     */
    protected $foreignTableField = self::DEFAULT_FOREIGN_TABLE_FIELD;

    /**
     * If set, it overrides the label set in TCA[foreign_table]['ctrl']['label']
     * for the foreign table view.
     *
     * @var string
     */
    protected $foreignLabel = self::DEFAULT_FOREIGN_LABEL;

    /**
     * A selector is used to show all possible child records that could be used
     * to create a relation with the parent record. It will be rendered as a
     * multi-select-box. On clicking on an item inside the selector a new relation
     * is created. The foreign_selector points to a field of the foreign_table that
     * is responsible for providing a selector-box – this field on the foreign_table
     * usually has the type "select" and also has a "foreign_table" defined.
     *
     * @var string
     */
    protected $foreignSelector = self::DEFAULT_FOREIGN_SELECTOR;

    /**
     * Defines a field on the child record (or on the intermediate table) that
     * stores the manual sorting information.
     *
     * @var string
     */
    protected $foreignSortby = self::DEFAULT_FOREIGN_SORTBY;

    /**
     * Allow manual sorting of child objects.
     *
     * @var boolean
     */
    protected $useSortable = self::DEFAULT_USE_SORTABLE;

    /**
     * Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete'
     * and 'localize'. Set either one to TRUE or FALSE to show or hide it.
     *
     * @var array
     */
    protected $enabledControls = [
        InlineFieldControls::INFO => false,
        InlineFieldControls::NEW => false,
        InlineFieldControls::DRAGDROP => true,
        InlineFieldControls::SORT => true,
        InlineFieldControls::HIDE => true,
        InlineFieldControls::DELETE => true,
        InlineFieldControls::LOCALIZE => true,
    ];

    /**
     * @var array
     */
    protected $headerThumbnail = [
        'field' => 'uid_local',
        'width' => '64',
        'height' => '64',
    ];

    /**
     * @var string
     */
    protected $levelLinksPosition = self::DEFAULT_LEVEL_LINKS_POSITION;

    /**
     * Set whether children can be localizable ('select') or just inherit from
     * default language ('keep'). Default is empty, meaning no particular behavior.
     *
     * @var string
     */
    protected $localizationMode = self::DEFAULT_LOCALIZATION_MODE;

    /**
     * Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')
     *
     * @var boolean
     */
    protected $newRecordLinkAddTitle = self::DEFAULT_NEW_RECORD_LINK_ADD_TITLE;

    /**
     * Label of 'create new relation' button
     *
     * @var string
     */
    protected $createNewRelationLinkTitle = self::DEFAULT_CREATE_NEW_RELATION_LINK_TITLE;

    /**
     * Crop variants for uploaded images
     *
     * @var array
     */
    protected $cropVariants = [];

    protected ?string $renderType = null;

    public function buildConfiguration(): array
    {
        $configuration = $this->prepareConfiguration('inline');
        $configuration['appearance']['createNewRelationLinkTitle'] = $this->getCreateNewRelationLinkTitle();

        if (!isset($configuration['overrideChildTca'])) {
            $configuration['overrideChildTca'] = ['columns' => []];
        }
        $configuration['overrideChildTca']['columns'] = $configuration['overrideChildTca']['columns'] ?? [];
        $configuration['overrideChildTca']['types'] = $configuration['foreign_types'];
        if (!empty($this->cropVariants)) {
            $configuration['overrideChildTca']['columns']['crop'] = [
                'config' => [
                    'cropVariants' => $this->cropVariants
                ]
            ];
        }

        return $configuration;
    }

    /**
     * @return string
     */
    public function getCreateNewRelationLinkTitle()
    {
        return $this->createNewRelationLinkTitle;
    }

    /**
     * @param string $createNewRelationLinkTitle
     * @return Fal
     */
    public function setCreateNewRelationLinkTitle($createNewRelationLinkTitle)
    {
        $this->createNewRelationLinkTitle = $createNewRelationLinkTitle;
        return $this;
    }

    /**
     * @return array
     */
    public function getCropVariants()
    {
        return $this->cropVariants;
    }

    /**
     * @param array $cropVariants
     * @return Fal
     */
    public function setCropVariants(array $cropVariants)
    {
        $this->cropVariants = $cropVariants;
        return $this;
    }
}
