.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-inline-fal:

================
field.inline.fal
================


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

Arguments
=========


.. _field.inline.fal_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the attribute, FlexForm XML-valid tag name string

.. _field.inline.fal_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _field.inline.fal_default:

default
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Default value for this attribute

.. _field.inline.fal_required:

required
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this attribute must be filled when editing the FCE

.. _field.inline.fal_exclude:

exclude
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _field.inline.fal_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _field.inline.fal_enabled:

enabled
-------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If FALSE, disables the field in the FlexForm

.. _field.inline.fal_requestupdate:

requestUpdate
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the form is force-saved and reloaded when field value changes

.. _field.inline.fal_displaycond:

displayCond
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _field.inline.fal_inherit:

inherit
-------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _field.inline.fal_inheritempty:

inheritEmpty
------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _field.inline.fal_clear:

clear
-----

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _field.inline.fal_variables:

variables
---------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _field.inline.fal_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _field.inline.fal_config:

config
------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _field.inline.fal_validate:

validate
--------

:aspect:`DataType`
   string

:aspect:`Default`
   'trim'

:aspect:`Required`
   false
:aspect:`Description`
   FlexForm-type validation configuration for this input

.. _field.inline.fal_size:

size
----

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Size of the selector box

.. _field.inline.fal_multiple:

multiple
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows selecting the same value multiple times

.. _field.inline.fal_minitems:

minItems
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Minimum required number of items to be selected

.. _field.inline.fal_maxitems:

maxItems
--------

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Maxium allowed number of items to be selected

.. _field.inline.fal_itemliststyle:

itemListStyle
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default list style when maxItems > 1

.. _field.inline.fal_selectedliststyle:

selectedListStyle
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _field.inline.fal_items:

items
-----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Items for the selector; array / CSV / Traversable / Query supported

.. _field.inline.fal_emptyoption:

emptyOption
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _field.inline.fal_translatecsvitems:

translateCsvItems
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _field.inline.fal_itemsprocfunc:

itemsProcFunc
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _field.inline.fal_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'sys_file_reference'

:aspect:`Required`
   false
:aspect:`Description`
   Define foreign table name to turn selector into a record selector for that table

.. _field.inline.fal_condition:

condition
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Condition to use when selecting from "foreignTable", supports FlexForm `foreign_table_where` markers

.. _field.inline.fal_mm:

mm
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of MM table to use for record selection

.. _field.inline.fal_foreignfield:

foreignField
------------

:aspect:`DataType`
   string

:aspect:`Default`
   'uid_foreign'

:aspect:`Required`
   false
:aspect:`Description`
   The foreign_field is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.

.. _field.inline.fal_foreignlabel:

foreignLabel
------------

:aspect:`DataType`
   string

:aspect:`Default`
   'uid_local'

:aspect:`Required`
   false
:aspect:`Description`
   If set, it overrides the label set in TCA[foreign_table]['ctrl']['label'] for the inline-view.

.. _field.inline.fal_foreignselector:

foreignSelector
---------------

:aspect:`DataType`
   string

:aspect:`Default`
   'uid_local'

:aspect:`Required`
   false
:aspect:`Description`
   A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The foreign_selector points to a field of the foreign_table that is responsible for providing a selector-box  this field on the foreign_table usually has the type "select" and also has a "foreign_table" defined.

.. _field.inline.fal_foreignsortby:

foreignSortby
-------------

:aspect:`DataType`
   string

:aspect:`Default`
   'sorting_foreign'

:aspect:`Required`
   false
:aspect:`Description`
   Field on the child record (or on the intermediate table) that stores the manual sorting information.

.. _field.inline.fal_foreigndefaultsortby:

foreignDefaultSortby
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If a fieldname for `foreign_sortby` is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.

.. _field.inline.fal_foreigntablefield:

foreignTableField
-----------------

:aspect:`DataType`
   string

:aspect:`Default`
   'tablenames'

:aspect:`Required`
   false
:aspect:`Description`
   The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with foreign_field, the child record knows what its parent record is - so the child record could also be used on other parent tables.

.. _field.inline.fal_foreignunique:

foreignUnique
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Field which must be uniue for all children of a parent record.

.. _field.inline.fal_symmetricfield:

symmetricField
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.

.. _field.inline.fal_symmetriclabel:

symmetricLabel
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If set, this overrides the default label of the selected `symmetric_field`.

.. _field.inline.fal_symmetricsortby:

symmetricSortby
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Works like `foreign_sortby`, but defines the field on `foreign_table` where the "other" sort order is stored.

.. _field.inline.fal_localizationmode:

localizationMode
----------------

:aspect:`DataType`
   string

:aspect:`Default`
   'select'

:aspect:`Required`
   false
:aspect:`Description`
   Set whether children can be localizable ('select') or just inherit from default language ('keep').

.. _field.inline.fal_localizechildrenatparentlocalization:

localizeChildrenAtParentLocalization
------------------------------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   Defines whether children should be localized when the localization of the parent gets created.

.. _field.inline.fal_disablemovingchildrenwithparent:

disableMovingChildrenWithParent
-------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Disables that child records get moved along with their parent records.

.. _field.inline.fal_showthumbs:

showThumbs
----------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, adds thumbnail display when editing in BE

.. _field.inline.fal_matchfields:

matchFields
-----------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   When using manyToMany you can provide an additional array of field=>value pairs that must match in the relation table

.. _field.inline.fal_oppositefield:

oppositeField
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the opposite field related to a proper mm relation

.. _field.inline.fal_collapseall:

collapseAll
-----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If true, all child records are shown as collapsed.

.. _field.inline.fal_expandsingle:

expandSingle
------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show only one expanded record at any time. If a new record is expanded, all others are collapsed.

.. _field.inline.fal_newrecordlinkaddtitle:

newRecordLinkAddTitle
---------------------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')

.. _field.inline.fal_newrecordlinkposition:

newRecordLinkPosition
---------------------

:aspect:`DataType`
   string

:aspect:`Default`
   'top'

:aspect:`Required`
   false
:aspect:`Description`
   Where to show 'Add new' link. Can be 'top', 'bottom', 'both' or 'none'.

.. _field.inline.fal_usecombination:

useCombination
--------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit '
           . 'attributes and the related child record.

.. _field.inline.fal_usesortable:

useSortable
-----------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   Allow manual sorting of records.

.. _field.inline.fal_showpossiblelocalizationrecords:

showPossibleLocalizationRecords
-------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show unlocalized records which are in the original language, but not yet localized.

.. _field.inline.fal_showremovedlocalizationrecords:

showRemovedLocalizationRecords
------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show records which were once localized but do not exist in the original language anymore.

.. _field.inline.fal_showalllocalizationlink:

showAllLocalizationLink
-----------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show the 'localize all records' link to fetch untranslated records from the original language.

.. _field.inline.fal_showsynchronizationlink:

showSynchronizationLink
-----------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.

.. _field.inline.fal_enabledcontrols:

enabledControls
---------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. '
           . 'Set either one to TRUE or FALSE to show or hide it.

.. _field.inline.fal_headerthumbnail:

headerThumbnail
---------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Associative array with header thumbnail.

.. _field.inline.fal_foreignmatchfields:

foreignMatchFields
------------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   The fields and values of the child record which have to match. For FAL the field/key is "fieldname" and the value has to be defined.

.. _field.inline.fal_foreigntypes:

foreignTypes
------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the "types" TCA array of the target table with this array (beware! must be specified fully in order to work!). Expects an array of arrays; indexed by type number - each array containing for example a "showitem" CSV list value of field names to be shown when inline-editing the related record.

.. _field.inline.fal_levellinksposition:

levelLinksPosition
------------------

:aspect:`DataType`
   string

:aspect:`Default`
   'both'

:aspect:`Required`
   false
:aspect:`Description`
   Level links position.

.. _field.inline.fal_allowedextensions:

allowedExtensions
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Allowed File Extensions .

.. _field.inline.fal_disallowedextensions:

disallowedExtensions
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Disallowed File Extensions .

.. _field.inline.fal_createnewrelationlinktitle:

createNewRelationLinkTitle
--------------------------

:aspect:`DataType`
   string

:aspect:`Default`
   'LLL:EXT:lang/locallang_core.xlf:cm.createNewRelation'

:aspect:`Required`
   false
:aspect:`Description`
   Override label of "Create new relation" button.

.. _field.inline.fal_cropvariants:

cropVariants
------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Add one or multiple crop variants for uploaded images
