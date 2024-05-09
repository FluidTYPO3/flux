..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/Inline/FalViewHelper.php

:edit-on-github-link: Field/Inline/FalViewHelper.php
:navigation-title: field.inline.fal
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-inline-fal:

=====================================================
Field.inline.fal ViewHelper `<flux:field.inline.fal>`
=====================================================

Creates a FAL IRRE field

To get the file references, assigned with that field in a flux form, you will have to use EXT:vhs but there are
two different ViewHelpers for fluidpages templates and fluidcontent elements.

Example how to get the first file reference from a fluidcontent element, for the flux field named "settings.files":

    {v:content.resources.fal(field: 'settings.files')
        -> v:iterator.first()
        -> v:variable.set(name: 'settings.files')}

And now the example how to get the first file reference from a fluidpages template, for the flux field
named "settings.files":

    {v:page.resources.fal(field: 'settings.files')
        -> v:iterator.first()
        -> v:variable.set(name: 'settings.files')}

Usage warning
=============

Due to [TYPO3 core bug #71239](https://forge.typo3.org/issues/71239), using
FAL references within sections (`<flux:form.section>`) in content elements
or within the page configuration does not work.

When choosing a file in one section element, you will see it in all sections.
When choosing a file in a page configuration, it will be visible in the subpages
configuration, too.

This issue will most likely not be fixed before TYPO3 8, so do not use it.

Alternatively, you could use `<flux:field.file>`.

Selecting and rendering an image
================================

Selecting a single image
------------------------

    <flux:field.inline.fal name="settings.image" required="1" maxItems="1" minItems="1"/>

Define crop variants
--------------------

    <flux:field.inline.fal name="settings.slides" required="1" maxItems="10" minItems="1" cropVariants="{
      default: {
        title: 'Default',
        allowedAspectRatios: {
          default: {
            title: '1200:450',
            value: '2.6666666666'
          }
        }
      }
    }"/>

The crop configuration can now be passed to the image viewhelper:

    <f:section name="Main">
      <f:for each="{v:content.resources.fal(field: 'settings.slides')}" as="image" iteration="iterator">
        <f:image src="{image.uid}" height="300" class="leb-pic" crop="{image.crop}" cropVariant="default"/>
      </f:for>
    </f:section>

Rendering the image
-------------------

    {v:content.resources.fal(field: 'settings.image') -> v:iterator.first() -> v:variable.set(name: 'image')}
    <f:image treatIdAsReference="1" src="{image.uid}" title="{image.title}" alt="{image.alternative}"/><br/>

Rendering multiple images
-------------------------

    <f:for each="{v:content.resources.fal(field: 'settings.image')}" as="image">
        <f:image treatIdAsReference="1" src="{image.uid}" title="{image.title}" alt="{image.alternative}"/><br/>
    </f:for>

.. _fluidtypo3-flux-field-inline-fal_source:

Source code
===========

Go to the source code of this ViewHelper: `FalViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/Inline/FalViewHelper.php>`__.

.. _fluidtypo3-flux-field-inline-fal_arguments:

Arguments
=========

The following arguments are available for `<flux:field.inline.fal>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-inline-fal-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-inline-fal-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-inline-fal-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-inline-fal-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-inline-fal-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-inline-fal-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-inline-fal-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-inline-fal-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-inline-fal-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-inline-fal-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-inline-fal-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-inline-fal-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-inline-fal-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-inline-fal-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-inline-fal-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-inline-fal-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-inline-fal-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-inline-fal-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-inline-fal-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-inline-fal-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-inline-fal-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-inline-fal-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-inline-fal-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-inline-fal-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-inline-fal-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-inline-fal-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-inline-fal-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-inline-fal-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-inline-fal-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-inline-fal-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-inline-fal-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-inline-fal-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-inline-fal-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-inline-fal-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-inline-fal-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-inline-fal-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-inline-fal-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-inline-fal-size
    :type: integer
    :Default: 1
    :required: false

    Size of the selector box

.. _fluidtypo3-flux-field-inline-fal-multiple_argument:

multiple
--------

..  confval:: multiple
    :name: fluidtypo3-flux-field-inline-fal-multiple
    :type: boolean
    :required: false

    If TRUE, allows selecting the same value multiple times

.. _fluidtypo3-flux-field-inline-fal-minitems_argument:

minItems
--------

..  confval:: minItems
    :name: fluidtypo3-flux-field-inline-fal-minitems
    :type: integer
    :required: false

    Minimum required number of items to be selected

.. _fluidtypo3-flux-field-inline-fal-maxitems_argument:

maxItems
--------

..  confval:: maxItems
    :name: fluidtypo3-flux-field-inline-fal-maxitems
    :type: integer
    :Default: 1
    :required: false

    Maxium allowed number of items to be selected

.. _fluidtypo3-flux-field-inline-fal-itemliststyle_argument:

itemListStyle
-------------

..  confval:: itemListStyle
    :name: fluidtypo3-flux-field-inline-fal-itemliststyle
    :type: string
    :required: false

    Overrides the default list style when maxItems > 1

.. _fluidtypo3-flux-field-inline-fal-selectedliststyle_argument:

selectedListStyle
-----------------

..  confval:: selectedListStyle
    :name: fluidtypo3-flux-field-inline-fal-selectedliststyle
    :type: string
    :required: false

    Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _fluidtypo3-flux-field-inline-fal-items_argument:

items
-----

..  confval:: items
    :name: fluidtypo3-flux-field-inline-fal-items
    :type: mixed
    :required: false

    Items for the selector; array / CSV / Traversable / Query supported

.. _fluidtypo3-flux-field-inline-fal-emptyoption_argument:

emptyOption
-----------

..  confval:: emptyOption
    :name: fluidtypo3-flux-field-inline-fal-emptyoption
    :type: mixed
    :required: false

    If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _fluidtypo3-flux-field-inline-fal-translatecsvitems_argument:

translateCsvItems
-----------------

..  confval:: translateCsvItems
    :name: fluidtypo3-flux-field-inline-fal-translatecsvitems
    :type: boolean
    :required: false

    If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _fluidtypo3-flux-field-inline-fal-itemsprocfunc_argument:

itemsProcFunc
-------------

..  confval:: itemsProcFunc
    :name: fluidtypo3-flux-field-inline-fal-itemsprocfunc
    :type: string
    :required: false

    Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _fluidtypo3-flux-field-inline-fal-table_argument:

table
-----

..  confval:: table
    :name: fluidtypo3-flux-field-inline-fal-table
    :type: string
    :Default: 'sys_file_reference'
    :required: false

    Define foreign table name to turn selector into a record selector for that table

.. _fluidtypo3-flux-field-inline-fal-condition_argument:

condition
---------

..  confval:: condition
    :name: fluidtypo3-flux-field-inline-fal-condition
    :type: string
    :required: false

    Condition to use when selecting from "foreignTable", supports FlexForm `foreign_table_where` markers

.. _fluidtypo3-flux-field-inline-fal-mm_argument:

mm
--

..  confval:: mm
    :name: fluidtypo3-flux-field-inline-fal-mm
    :type: string
    :required: false

    Optional name of MM table to use for record selection

.. _fluidtypo3-flux-field-inline-fal-foreignfield_argument:

foreignField
------------

..  confval:: foreignField
    :name: fluidtypo3-flux-field-inline-fal-foreignfield
    :type: string
    :Default: 'uid_foreign'
    :required: false

    The foreign_field is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.

.. _fluidtypo3-flux-field-inline-fal-foreignlabel_argument:

foreignLabel
------------

..  confval:: foreignLabel
    :name: fluidtypo3-flux-field-inline-fal-foreignlabel
    :type: string
    :Default: 'uid_local'
    :required: false

    If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.

.. _fluidtypo3-flux-field-inline-fal-foreignselector_argument:

foreignSelector
---------------

..  confval:: foreignSelector
    :name: fluidtypo3-flux-field-inline-fal-foreignselector
    :type: string
    :Default: 'uid_local'
    :required: false

    A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The foreign_selector points to a field of the foreign_table that is responsible for providing a selector-box  this field on the foreign_table usually has the type "select" and also has a "foreign_table" defined.

.. _fluidtypo3-flux-field-inline-fal-foreignsortby_argument:

foreignSortby
-------------

..  confval:: foreignSortby
    :name: fluidtypo3-flux-field-inline-fal-foreignsortby
    :type: string
    :Default: 'sorting_foreign'
    :required: false

    Field on the child record (or on the intermediate table) that stores the manual sorting information.

.. _fluidtypo3-flux-field-inline-fal-foreigndefaultsortby_argument:

foreignDefaultSortby
--------------------

..  confval:: foreignDefaultSortby
    :name: fluidtypo3-flux-field-inline-fal-foreigndefaultsortby
    :type: string
    :required: false

    If a fieldname for `foreign_sortby` is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.

.. _fluidtypo3-flux-field-inline-fal-foreigntablefield_argument:

foreignTableField
-----------------

..  confval:: foreignTableField
    :name: fluidtypo3-flux-field-inline-fal-foreigntablefield
    :type: string
    :Default: 'tablenames'
    :required: false

    The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with foreign_field, the child record knows what its parent record is - so the child record could also be used on other parent tables.

.. _fluidtypo3-flux-field-inline-fal-foreignunique_argument:

foreignUnique
-------------

..  confval:: foreignUnique
    :name: fluidtypo3-flux-field-inline-fal-foreignunique
    :type: string
    :required: false

    Field which must be uniue for all children of a parent record.

.. _fluidtypo3-flux-field-inline-fal-symmetricfield_argument:

symmetricField
--------------

..  confval:: symmetricField
    :name: fluidtypo3-flux-field-inline-fal-symmetricfield
    :type: string
    :required: false

    In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.

.. _fluidtypo3-flux-field-inline-fal-symmetriclabel_argument:

symmetricLabel
--------------

..  confval:: symmetricLabel
    :name: fluidtypo3-flux-field-inline-fal-symmetriclabel
    :type: string
    :required: false

    If set, this overrides the default label of the selected `symmetric_field`.

.. _fluidtypo3-flux-field-inline-fal-symmetricsortby_argument:

symmetricSortby
---------------

..  confval:: symmetricSortby
    :name: fluidtypo3-flux-field-inline-fal-symmetricsortby
    :type: string
    :required: false

    Works like `foreign_sortby`, but defines the field on `foreign_table` where the "other" sort order is stored.

.. _fluidtypo3-flux-field-inline-fal-localizationmode_argument:

localizationMode
----------------

..  confval:: localizationMode
    :name: fluidtypo3-flux-field-inline-fal-localizationmode
    :type: string
    :Default: 'select'
    :required: false

    Set whether children can be localizable ('select') or just inherit from default language ('keep').

.. _fluidtypo3-flux-field-inline-fal-disablemovingchildrenwithparent_argument:

disableMovingChildrenWithParent
-------------------------------

..  confval:: disableMovingChildrenWithParent
    :name: fluidtypo3-flux-field-inline-fal-disablemovingchildrenwithparent
    :type: boolean
    :required: false

    Disables that child records get moved along with their parent records.

.. _fluidtypo3-flux-field-inline-fal-showthumbs_argument:

showThumbs
----------

..  confval:: showThumbs
    :name: fluidtypo3-flux-field-inline-fal-showthumbs
    :type: boolean
    :Default: true
    :required: false

    If TRUE, adds thumbnail display when editing in BE

.. _fluidtypo3-flux-field-inline-fal-matchfields_argument:

matchFields
-----------

..  confval:: matchFields
    :name: fluidtypo3-flux-field-inline-fal-matchfields
    :type: mixed
    :Default: array ()
    :required: false

    When using manyToMany you can provide an additional array of field=>value pairs that must match in the relation table

.. _fluidtypo3-flux-field-inline-fal-oppositefield_argument:

oppositeField
-------------

..  confval:: oppositeField
    :name: fluidtypo3-flux-field-inline-fal-oppositefield
    :type: string
    :required: false

    Name of the opposite field related to a proper mm relation

.. _fluidtypo3-flux-field-inline-fal-collapseall_argument:

collapseAll
-----------

..  confval:: collapseAll
    :name: fluidtypo3-flux-field-inline-fal-collapseall
    :type: boolean
    :required: false

    If true, all child records are shown as collapsed.

.. _fluidtypo3-flux-field-inline-fal-expandsingle_argument:

expandSingle
------------

..  confval:: expandSingle
    :name: fluidtypo3-flux-field-inline-fal-expandsingle
    :type: boolean
    :required: false

    Show only one expanded record at any time. If a new record is expanded, all others are collapsed.

.. _fluidtypo3-flux-field-inline-fal-newrecordlinkaddtitle_argument:

newRecordLinkAddTitle
---------------------

..  confval:: newRecordLinkAddTitle
    :name: fluidtypo3-flux-field-inline-fal-newrecordlinkaddtitle
    :type: boolean
    :Default: true
    :required: false

    Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')

.. _fluidtypo3-flux-field-inline-fal-newrecordlinkposition_argument:

newRecordLinkPosition
---------------------

..  confval:: newRecordLinkPosition
    :name: fluidtypo3-flux-field-inline-fal-newrecordlinkposition
    :type: string
    :Default: 'top'
    :required: false

    Where to show 'Add new' link. Can be 'top', 'bottom', 'both' or 'none'.

.. _fluidtypo3-flux-field-inline-fal-usecombination_argument:

useCombination
--------------

..  confval:: useCombination
    :name: fluidtypo3-flux-field-inline-fal-usecombination
    :type: boolean
    :required: false

    For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit '
           . 'attributes and the related child record.

.. _fluidtypo3-flux-field-inline-fal-usesortable_argument:

useSortable
-----------

..  confval:: useSortable
    :name: fluidtypo3-flux-field-inline-fal-usesortable
    :type: boolean
    :Default: true
    :required: false

    Allow manual sorting of records.

.. _fluidtypo3-flux-field-inline-fal-showpossiblelocalizationrecords_argument:

showPossibleLocalizationRecords
-------------------------------

..  confval:: showPossibleLocalizationRecords
    :name: fluidtypo3-flux-field-inline-fal-showpossiblelocalizationrecords
    :type: boolean
    :required: false

    Show unlocalized records which are in the original language, but not yet localized.

.. _fluidtypo3-flux-field-inline-fal-showremovedlocalizationrecords_argument:

showRemovedLocalizationRecords
------------------------------

..  confval:: showRemovedLocalizationRecords
    :name: fluidtypo3-flux-field-inline-fal-showremovedlocalizationrecords
    :type: boolean
    :required: false

    Show records which were once localized but do not exist in the original language anymore.

.. _fluidtypo3-flux-field-inline-fal-showalllocalizationlink_argument:

showAllLocalizationLink
-----------------------

..  confval:: showAllLocalizationLink
    :name: fluidtypo3-flux-field-inline-fal-showalllocalizationlink
    :type: boolean
    :required: false

    Show the 'localize all records' link to fetch untranslated records from the original language.

.. _fluidtypo3-flux-field-inline-fal-showsynchronizationlink_argument:

showSynchronizationLink
-----------------------

..  confval:: showSynchronizationLink
    :name: fluidtypo3-flux-field-inline-fal-showsynchronizationlink
    :type: boolean
    :required: false

    Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.

.. _fluidtypo3-flux-field-inline-fal-enabledcontrols_argument:

enabledControls
---------------

..  confval:: enabledControls
    :name: fluidtypo3-flux-field-inline-fal-enabledcontrols
    :type: mixed
    :required: false

    Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. '
           . 'Set either one to TRUE or FALSE to show or hide it.

.. _fluidtypo3-flux-field-inline-fal-headerthumbnail_argument:

headerThumbnail
---------------

..  confval:: headerThumbnail
    :name: fluidtypo3-flux-field-inline-fal-headerthumbnail
    :type: mixed
    :required: false

    Associative array with header thumbnail.

.. _fluidtypo3-flux-field-inline-fal-foreignmatchfields_argument:

foreignMatchFields
------------------

..  confval:: foreignMatchFields
    :name: fluidtypo3-flux-field-inline-fal-foreignmatchfields
    :type: mixed
    :required: false

    The fields and values of the child record which have to match. For FAL the field/key is "fieldname" and the value has to be defined.

.. _fluidtypo3-flux-field-inline-fal-foreigntypes_argument:

foreignTypes
------------

..  confval:: foreignTypes
    :name: fluidtypo3-flux-field-inline-fal-foreigntypes
    :type: mixed
    :required: false

    Overrides the "types" TCA array of the target table with this array (beware! must be specified fully in order to work!). Expects an array of arrays; indexed by type number - each array containing for example a "showitem" CSV list value of field names to be shown when inline-editing the related record.

.. _fluidtypo3-flux-field-inline-fal-levellinksposition_argument:

levelLinksPosition
------------------

..  confval:: levelLinksPosition
    :name: fluidtypo3-flux-field-inline-fal-levellinksposition
    :type: string
    :Default: 'both'
    :required: false

    Level links position.

.. _fluidtypo3-flux-field-inline-fal-allowedextensions_argument:

allowedExtensions
-----------------

..  confval:: allowedExtensions
    :name: fluidtypo3-flux-field-inline-fal-allowedextensions
    :type: string
    :required: false

    Allowed File Extensions .

.. _fluidtypo3-flux-field-inline-fal-disallowedextensions_argument:

disallowedExtensions
--------------------

..  confval:: disallowedExtensions
    :name: fluidtypo3-flux-field-inline-fal-disallowedextensions
    :type: string
    :required: false

    Disallowed File Extensions .

.. _fluidtypo3-flux-field-inline-fal-createnewrelationlinktitle_argument:

createNewRelationLinkTitle
--------------------------

..  confval:: createNewRelationLinkTitle
    :name: fluidtypo3-flux-field-inline-fal-createnewrelationlinktitle
    :type: string
    :Default: 'LLL:EXT:lang/locallang_core.xlf:cm.createNewRelation'
    :required: false

    Override label of "Create new relation" button.

.. _fluidtypo3-flux-field-inline-fal-cropvariants_argument:

cropVariants
------------

..  confval:: cropVariants
    :name: fluidtypo3-flux-field-inline-fal-cropvariants
    :type: mixed
    :required: false

    Add one or multiple crop variants for uploaded images
