..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/Tree/CategoryViewHelper.php

:edit-on-github-link: Field/Tree/CategoryViewHelper.php
:navigation-title: field.tree.category
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-tree-category:

===========================================================
Field.tree.category ViewHelper `<flux:field.tree.category>`
===========================================================

Tree preset for sys_category

.. _fluidtypo3-flux-field-tree-category_source:

Source code
===========

Go to the source code of this ViewHelper: `CategoryViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/Tree/CategoryViewHelper.php>`__.

.. _fluidtypo3-flux-field-tree-category_arguments:

Arguments
=========

The following arguments are available for `<flux:field.tree.category>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-tree-category-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-tree-category-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-tree-category-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-tree-category-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-tree-category-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-tree-category-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-tree-category-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-tree-category-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-tree-category-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-tree-category-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-tree-category-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-tree-category-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-tree-category-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-tree-category-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-tree-category-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-tree-category-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-tree-category-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-tree-category-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-tree-category-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-tree-category-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-tree-category-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-tree-category-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-tree-category-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-tree-category-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-tree-category-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-tree-category-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-tree-category-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-tree-category-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-tree-category-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-tree-category-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-tree-category-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-tree-category-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-tree-category-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-tree-category-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-tree-category-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-tree-category-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-tree-category-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-tree-category-size
    :type: integer
    :Default: 10
    :required: false

    Size of the selector box

.. _fluidtypo3-flux-field-tree-category-multiple_argument:

multiple
--------

..  confval:: multiple
    :name: fluidtypo3-flux-field-tree-category-multiple
    :type: boolean
    :required: false

    If TRUE, allows selecting the same value multiple times

.. _fluidtypo3-flux-field-tree-category-minitems_argument:

minItems
--------

..  confval:: minItems
    :name: fluidtypo3-flux-field-tree-category-minitems
    :type: integer
    :required: false

    Minimum required number of items to be selected

.. _fluidtypo3-flux-field-tree-category-maxitems_argument:

maxItems
--------

..  confval:: maxItems
    :name: fluidtypo3-flux-field-tree-category-maxitems
    :type: integer
    :Default: 30
    :required: false

    Maxium allowed number of items to be selected

.. _fluidtypo3-flux-field-tree-category-itemliststyle_argument:

itemListStyle
-------------

..  confval:: itemListStyle
    :name: fluidtypo3-flux-field-tree-category-itemliststyle
    :type: string
    :required: false

    Overrides the default list style when maxItems > 1

.. _fluidtypo3-flux-field-tree-category-selectedliststyle_argument:

selectedListStyle
-----------------

..  confval:: selectedListStyle
    :name: fluidtypo3-flux-field-tree-category-selectedliststyle
    :type: string
    :required: false

    Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _fluidtypo3-flux-field-tree-category-items_argument:

items
-----

..  confval:: items
    :name: fluidtypo3-flux-field-tree-category-items
    :type: mixed
    :required: false

    Items for the selector; array / CSV / Traversable / Query supported

.. _fluidtypo3-flux-field-tree-category-emptyoption_argument:

emptyOption
-----------

..  confval:: emptyOption
    :name: fluidtypo3-flux-field-tree-category-emptyoption
    :type: mixed
    :required: false

    If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _fluidtypo3-flux-field-tree-category-translatecsvitems_argument:

translateCsvItems
-----------------

..  confval:: translateCsvItems
    :name: fluidtypo3-flux-field-tree-category-translatecsvitems
    :type: boolean
    :required: false

    If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _fluidtypo3-flux-field-tree-category-itemsprocfunc_argument:

itemsProcFunc
-------------

..  confval:: itemsProcFunc
    :name: fluidtypo3-flux-field-tree-category-itemsprocfunc
    :type: string
    :required: false

    Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _fluidtypo3-flux-field-tree-category-table_argument:

table
-----

..  confval:: table
    :name: fluidtypo3-flux-field-tree-category-table
    :type: string
    :Default: 'sys_category'
    :required: false

    Define foreign table name to turn selector into a record selector for that table

.. _fluidtypo3-flux-field-tree-category-condition_argument:

condition
---------

..  confval:: condition
    :name: fluidtypo3-flux-field-tree-category-condition
    :type: string
    :required: false

    Condition to use when selecting from "foreignTable", supports FlexForm `foreign_table_where` markers

.. _fluidtypo3-flux-field-tree-category-mm_argument:

mm
--

..  confval:: mm
    :name: fluidtypo3-flux-field-tree-category-mm
    :type: string
    :Default: 'sys_category_record_mm'
    :required: false

    Optional name of MM table to use for record selection

.. _fluidtypo3-flux-field-tree-category-foreignfield_argument:

foreignField
------------

..  confval:: foreignField
    :name: fluidtypo3-flux-field-tree-category-foreignfield
    :type: string
    :required: false

    The `foreign_field` is the field of the child record pointing to the parent record. This defines where to store the uid of the parent record.

.. _fluidtypo3-flux-field-tree-category-foreignlabel_argument:

foreignLabel
------------

..  confval:: foreignLabel
    :name: fluidtypo3-flux-field-tree-category-foreignlabel
    :type: string
    :required: false

    If set, it overrides the label set in `TCA[foreign_table]['ctrl']['label']` for the inline-view.

.. _fluidtypo3-flux-field-tree-category-foreignselector_argument:

foreignSelector
---------------

..  confval:: foreignSelector
    :name: fluidtypo3-flux-field-tree-category-foreignselector
    :type: string
    :required: false

    A selector is used to show all possible child records that could be used to create a relation with the parent record. It will be rendered as a multi-select-box. On clicking on an item inside the selector a new relation is created. The `foreign_selector` points to a field of the `foreign_table` that is responsible for providing a selector-box - this field on the `foreign_table` usually has the type "select" and also has a `foreign_table` defined.

.. _fluidtypo3-flux-field-tree-category-foreignsortby_argument:

foreignSortby
-------------

..  confval:: foreignSortby
    :name: fluidtypo3-flux-field-tree-category-foreignsortby
    :type: string
    :required: false

    Field on the child record (or on the intermediate table) that stores the manual sorting information.

.. _fluidtypo3-flux-field-tree-category-foreigndefaultsortby_argument:

foreignDefaultSortby
--------------------

..  confval:: foreignDefaultSortby
    :name: fluidtypo3-flux-field-tree-category-foreigndefaultsortby
    :type: string
    :required: false

    If a fieldname for `foreign_sortby` is defined, then this is ignored. Otherwise this is used as the "ORDER BY" statement to sort the records in the table when listed.

.. _fluidtypo3-flux-field-tree-category-foreigntablefield_argument:

foreignTableField
-----------------

..  confval:: foreignTableField
    :name: fluidtypo3-flux-field-tree-category-foreigntablefield
    :type: string
    :required: false

    The field of the child record pointing to the parent record. This defines where to store the table name of the parent record. On setting this configuration key together with `foreign_field`, the child record knows what its parent record is - so the child record could also be used on other parent tables.

.. _fluidtypo3-flux-field-tree-category-foreignunique_argument:

foreignUnique
-------------

..  confval:: foreignUnique
    :name: fluidtypo3-flux-field-tree-category-foreignunique
    :type: string
    :required: false

    Field which must be uniue for all children of a parent record.

.. _fluidtypo3-flux-field-tree-category-symmetricfield_argument:

symmetricField
--------------

..  confval:: symmetricField
    :name: fluidtypo3-flux-field-tree-category-symmetricfield
    :type: string
    :required: false

    In case of bidirectional symmetric relations, this defines in which field on the foreign table the uid of the "other" parent is stored.

.. _fluidtypo3-flux-field-tree-category-symmetriclabel_argument:

symmetricLabel
--------------

..  confval:: symmetricLabel
    :name: fluidtypo3-flux-field-tree-category-symmetriclabel
    :type: string
    :required: false

    If set, this overrides the default label of the selected `symmetric_field`.

.. _fluidtypo3-flux-field-tree-category-symmetricsortby_argument:

symmetricSortby
---------------

..  confval:: symmetricSortby
    :name: fluidtypo3-flux-field-tree-category-symmetricsortby
    :type: string
    :required: false

    Works like `foreign_sortby`, but defines the field on `foreign_table` where the "other" sort order is stored.

.. _fluidtypo3-flux-field-tree-category-localizationmode_argument:

localizationMode
----------------

..  confval:: localizationMode
    :name: fluidtypo3-flux-field-tree-category-localizationmode
    :type: string
    :required: false

    Set whether children can be localizable ('select') or just inherit from default language ('keep').

.. _fluidtypo3-flux-field-tree-category-disablemovingchildrenwithparent_argument:

disableMovingChildrenWithParent
-------------------------------

..  confval:: disableMovingChildrenWithParent
    :name: fluidtypo3-flux-field-tree-category-disablemovingchildrenwithparent
    :type: boolean
    :required: false

    Disables that child records get moved along with their parent records.

.. _fluidtypo3-flux-field-tree-category-showthumbs_argument:

showThumbs
----------

..  confval:: showThumbs
    :name: fluidtypo3-flux-field-tree-category-showthumbs
    :type: boolean
    :Default: true
    :required: false

    If TRUE, adds thumbnail display when editing in BE

.. _fluidtypo3-flux-field-tree-category-matchfields_argument:

matchFields
-----------

..  confval:: matchFields
    :name: fluidtypo3-flux-field-tree-category-matchfields
    :type: mixed
    :required: false

    When using manyToMany you can provide an additional array of field=>value pairs that must match in the relation table

.. _fluidtypo3-flux-field-tree-category-oppositefield_argument:

oppositeField
-------------

..  confval:: oppositeField
    :name: fluidtypo3-flux-field-tree-category-oppositefield
    :type: string
    :Default: 'items'
    :required: false

    Name of the opposite field related to a proper mm relation

.. _fluidtypo3-flux-field-tree-category-parentfield_argument:

parentField
-----------

..  confval:: parentField
    :name: fluidtypo3-flux-field-tree-category-parentfield
    :type: string
    :Default: 'parent'
    :required: false

    Field containing UID of parent record

.. _fluidtypo3-flux-field-tree-category-allowrecursivemode_argument:

allowRecursiveMode
------------------

..  confval:: allowRecursiveMode
    :name: fluidtypo3-flux-field-tree-category-allowrecursivemode
    :type: boolean
    :required: false

    If TRUE, the selection of a node will trigger the selection of all child nodes too (recursively)

.. _fluidtypo3-flux-field-tree-category-expandall_argument:

expandAll
---------

..  confval:: expandAll
    :name: fluidtypo3-flux-field-tree-category-expandall
    :type: boolean
    :Default: true
    :required: false

    If TRUE, expands all branches

.. _fluidtypo3-flux-field-tree-category-nonselectablelevels_argument:

nonSelectableLevels
-------------------

..  confval:: nonSelectableLevels
    :name: fluidtypo3-flux-field-tree-category-nonselectablelevels
    :type: string
    :required: false

    Comma-separated list of levels that will not be selectable, by default the root node (which is "0") cannot be selected

.. _fluidtypo3-flux-field-tree-category-maxlevels_argument:

maxLevels
---------

..  confval:: maxLevels
    :name: fluidtypo3-flux-field-tree-category-maxlevels
    :type: integer
    :Default: 2
    :required: false

    The maximal amount of levels to be rendered (can be used to stop possible recursions)

.. _fluidtypo3-flux-field-tree-category-showheader_argument:

showHeader
----------

..  confval:: showHeader
    :name: fluidtypo3-flux-field-tree-category-showheader
    :type: boolean
    :Default: true
    :required: false

    If TRUE, displays tree header

.. _fluidtypo3-flux-field-tree-category-width_argument:

width
-----

..  confval:: width
    :name: fluidtypo3-flux-field-tree-category-width
    :type: integer
    :Default: 280
    :required: false

    Width of TreeView component
