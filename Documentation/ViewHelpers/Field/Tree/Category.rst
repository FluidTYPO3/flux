.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-tree-category:

===================
field.tree.category
===================


Tree preset for sys_category

Arguments
=========


.. _field.tree.category_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the attribute, FlexForm XML-valid tag name string

.. _field.tree.category_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _field.tree.category_default:

default
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Default value for this attribute

.. _field.tree.category_native:

native
------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _field.tree.category_position:

position
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _field.tree.category_required:

required
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this attribute must be filled when editing the FCE

.. _field.tree.category_exclude:

exclude
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _field.tree.category_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _field.tree.category_enabled:

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

.. _field.tree.category_requestupdate:

requestUpdate
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the form is force-saved and reloaded when field value changes

.. _field.tree.category_displaycond:

displayCond
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _field.tree.category_inherit:

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

.. _field.tree.category_inheritempty:

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

.. _field.tree.category_clear:

clear
-----

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _field.tree.category_variables:

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

.. _field.tree.category_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _field.tree.category_config:

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

.. _field.tree.category_validate:

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

.. _field.tree.category_size:

size
----

:aspect:`DataType`
   integer

:aspect:`Default`
   10

:aspect:`Required`
   false
:aspect:`Description`
   Size of the selector box

.. _field.tree.category_multiple:

multiple
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows selecting the same value multiple times

.. _field.tree.category_minitems:

minItems
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Minimum required number of items to be selected

.. _field.tree.category_maxitems:

maxItems
--------

:aspect:`DataType`
   integer

:aspect:`Default`
   30

:aspect:`Required`
   false
:aspect:`Description`
   Maxium allowed number of items to be selected

.. _field.tree.category_itemliststyle:

itemListStyle
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default list style when maxItems > 1

.. _field.tree.category_selectedliststyle:

selectedListStyle
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _field.tree.category_items:

items
-----

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   Items for the selector; array / CSV / Traversable / Query supported

.. _field.tree.category_emptyoption:

emptyOption
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _field.tree.category_translatecsvitems:

translateCsvItems
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _field.tree.category_itemsprocfunc:

itemsProcFunc
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _field.tree.category_table:

table
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'sys_category'

:aspect:`Required`
   false
:aspect:`Description`
   Define foreign table name to turn selector into a record selector for that table

.. _field.tree.category_condition:

condition
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Condition to use when selecting from "foreignTable", supports FlexForm `foreign_table_where` markers

.. _field.tree.category_mm:

mm
--

:aspect:`DataType`
   string

:aspect:`Default`
   'sys_category_record_mm'

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of MM table to use for record selection

.. _field.tree.category_foreignfield:

foreignField
------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The `foreign_field` is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.

.. _field.tree.category_foreignlabel:

foreignLabel
------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If set, it overrides the label set in `TCA[foreign_table]['ctrl']['label']` for the inline-view.

.. _field.tree.category_foreignselector:

foreignSelector
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The `foreign_selector` points to a field of the `foreign_table` that is responsible for providing a selector-box - this field on the `foreign_table` usually has the type "select" and also has a `foreign_table` defined.

.. _field.tree.category_foreignsortby:

foreignSortby
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Field on the child record (or on the intermediate table) that stores the manual sorting information.

.. _field.tree.category_foreigndefaultsortby:

foreignDefaultSortby
--------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If a fieldname for `foreign_sortby` is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.

.. _field.tree.category_foreigntablefield:

foreignTableField
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with `foreign_field`, the child record knows what its parent record is - so the child record could also be used on other parent tables.

.. _field.tree.category_foreignunique:

foreignUnique
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Field which must be uniue for all children of a parent record.

.. _field.tree.category_symmetricfield:

symmetricField
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.

.. _field.tree.category_symmetriclabel:

symmetricLabel
--------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If set, this overrides the default label of the selected `symmetric_field`.

.. _field.tree.category_symmetricsortby:

symmetricSortby
---------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Works like `foreign_sortby`, but defines the field on `foreign_table` where the "other" sort order is stored.

.. _field.tree.category_localizationmode:

localizationMode
----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set whether children can be localizable ('select') or just inherit from default language ('keep').

.. _field.tree.category_disablemovingchildrenwithparent:

disableMovingChildrenWithParent
-------------------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Disables that child records get moved along with their parent records.

.. _field.tree.category_showthumbs:

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

.. _field.tree.category_matchfields:

matchFields
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   When using manyToMany you can provide an additional array of field=>value pairs that must match in the relation table

.. _field.tree.category_oppositefield:

oppositeField
-------------

:aspect:`DataType`
   string

:aspect:`Default`
   'items'

:aspect:`Required`
   false
:aspect:`Description`
   Name of the opposite field related to a proper mm relation

.. _field.tree.category_parentfield:

parentField
-----------

:aspect:`DataType`
   string

:aspect:`Default`
   'parent'

:aspect:`Required`
   false
:aspect:`Description`
   Field containing UID of parent record

.. _field.tree.category_allowrecursivemode:

allowRecursiveMode
------------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the selection of a node will trigger the selection of all child nodes too (recursively)

.. _field.tree.category_expandall:

expandAll
---------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, expands all branches

.. _field.tree.category_nonselectablelevels:

nonSelectableLevels
-------------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Comma-separated list of levels that will not be selectable, by default the root node (which is "0") cannot be selected

.. _field.tree.category_maxlevels:

maxLevels
---------

:aspect:`DataType`
   integer

:aspect:`Default`
   2

:aspect:`Required`
   false
:aspect:`Description`
   The maximal amount of levels to be rendered (can be used to stop possible recursions)

.. _field.tree.category_showheader:

showHeader
----------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, displays tree header

.. _field.tree.category_width:

width
-----

:aspect:`DataType`
   integer

:aspect:`Default`
   280

:aspect:`Required`
   false
:aspect:`Description`
   Width of TreeView component
