.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-inline:

============
field.inline
============


Inline-style FlexForm field ViewHelper

DEPRECATED - use flux:field instead

Arguments
=========


.. _field.inline_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the attribute, FlexForm XML-valid tag name string

.. _field.inline_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _field.inline_default:

default
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Default value for this attribute

.. _field.inline_native:

native
------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _field.inline_position:

position
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _field.inline_required:

required
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this attribute must be filled when editing the FCE

.. _field.inline_exclude:

exclude
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _field.inline_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _field.inline_enabled:

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

.. _field.inline_requestupdate:

requestUpdate
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the form is force-saved and reloaded when field value changes

.. _field.inline_displaycond:

displayCond
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _field.inline_inherit:

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

.. _field.inline_inheritempty:

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

.. _field.inline_clear:

clear
-----

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _field.inline_variables:

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

.. _field.inline_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _field.inline_config:

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

.. _field.inline_validate:

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

.. _field.inline_size:

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

.. _field.inline_multiple:

multiple
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows selecting the same value multiple times

.. _field.inline_minitems:

minItems
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Minimum required number of items to be selected

.. _field.inline_maxitems:

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

.. _field.inline_itemliststyle:

itemListStyle
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default list style when maxItems > 1

.. _field.inline_selectedliststyle:

selectedListStyle
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _field.inline_items:

items
-----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Items for the selector; array / CSV / Traversable / Query supported

.. _field.inline_emptyoption:

emptyOption
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _field.inline_translatecsvitems:

translateCsvItems
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _field.inline_itemsprocfunc:

itemsProcFunc
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _field.inline_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Define foreign table name to turn selector into a record selector for that table

.. _field.inline_condition:

condition
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Condition to use when selecting from "foreignTable", supports FlexForm `foreign_table_where` markers

.. _field.inline_mm:

mm
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of MM table to use for record selection

.. _field.inline_foreignfield:

foreignField
------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The `foreign_field` is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.

.. _field.inline_foreignlabel:

foreignLabel
------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If set, it overrides the label set in `TCA[foreign_table]['ctrl']['label']` for the inline-view.

.. _field.inline_foreignselector:

foreignSelector
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The `foreign_selector` points to a field of the `foreign_table` that is responsible for providing a selector-box - this field on the `foreign_table` usually has the type "select" and also has a `foreign_table` defined.

.. _field.inline_foreignsortby:

foreignSortby
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Field on the child record (or on the intermediate table) that stores the manual sorting information.

.. _field.inline_foreigndefaultsortby:

foreignDefaultSortby
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If a fieldname for `foreign_sortby` is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.

.. _field.inline_foreigntablefield:

foreignTableField
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with `foreign_field`, the child record knows what its parent record is - so the child record could also be used on other parent tables.

.. _field.inline_foreignunique:

foreignUnique
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Field which must be uniue for all children of a parent record.

.. _field.inline_symmetricfield:

symmetricField
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.

.. _field.inline_symmetriclabel:

symmetricLabel
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If set, this overrides the default label of the selected `symmetric_field`.

.. _field.inline_symmetricsortby:

symmetricSortby
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Works like `foreign_sortby`, but defines the field on `foreign_table` where the "other" sort order is stored.

.. _field.inline_localizationmode:

localizationMode
----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set whether children can be localizable ('select') or just inherit from default language ('keep').

.. _field.inline_disablemovingchildrenwithparent:

disableMovingChildrenWithParent
-------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Disables that child records get moved along with their parent records.

.. _field.inline_showthumbs:

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

.. _field.inline_matchfields:

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

.. _field.inline_oppositefield:

oppositeField
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Name of the opposite field related to a proper mm relation

.. _field.inline_collapseall:

collapseAll
-----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If true, all child records are shown as collapsed.

.. _field.inline_expandsingle:

expandSingle
------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show only one expanded record at any time. If a new record is expanded, all others are collapsed.

.. _field.inline_newrecordlinkaddtitle:

newRecordLinkAddTitle
---------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Add the foreign table's title to the 'Add new' link (ie. 'Add new (sometable)')

.. _field.inline_newrecordlinkposition:

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

.. _field.inline_usecombination:

useCombination
--------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   For use on bidirectional relations using an intermediary table. In combinations, it's possible to edit '
           . 'attributes and the related child record.

.. _field.inline_usesortable:

useSortable
-----------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Allow manual sorting of records.

.. _field.inline_showpossiblelocalizationrecords:

showPossibleLocalizationRecords
-------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show unlocalized records which are in the original language, but not yet localized.

.. _field.inline_showremovedlocalizationrecords:

showRemovedLocalizationRecords
------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show records which were once localized but do not exist in the original language anymore.

.. _field.inline_showalllocalizationlink:

showAllLocalizationLink
-----------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Show the 'localize all records' link to fetch untranslated records from the original language.

.. _field.inline_showsynchronizationlink:

showSynchronizationLink
-----------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Defines whether to show a 'synchronize' link to update to a 1:1 translation with the original language.

.. _field.inline_enabledcontrols:

enabledControls
---------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Associative array with the keys 'info', 'new', 'dragdrop', 'sort', 'hide', delete' and 'localize'. '
           . 'Set either one to TRUE or FALSE to show or hide it.

.. _field.inline_headerthumbnail:

headerThumbnail
---------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Associative array with header thumbnail.

.. _field.inline_foreignmatchfields:

foreignMatchFields
------------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   The fields and values of the child record which have to match. For FAL the field/key is "fieldname" and the value has to be defined.

.. _field.inline_foreigntypes:

foreignTypes
------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the "types" TCA array of the target table with this array (beware! must be specified fully in order to work!). Expects an array of arrays; indexed by type number - each array containing for example a "showitem" CSV list value of field names to be shown when inline-editing the related record.

.. _field.inline_levellinksposition:

levelLinksPosition
------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Level links position.
