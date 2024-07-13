:navigation-title: field.select
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-select:

=============================================
field.select ViewHelper `<flux:field.select>`
=============================================


Select-type FlexForm field ViewHelper

Choosing one of two items
=========================

Items are given in CSV mode:

    <flux:field.select name="settings.position" items="left,right" default="left"/>

Items with labels
=================

If you want to display labels that are different than the values itself,
use an object in `items`:

     <flux:field.select name="settings.position"
                        items="{
                               0:{0:'On the left side',1:'left'},
                               1:{0:'On the right side',1:'right'}
                               }"
                       />

You can translate those labels by putting a LLL reference in the first property:

    LLL:EXT:extname/Resources/Private/Language/locallang.xlf:flux.example.fields.items.foo'

Links
=====

* [TCA Reference: type "select"](https://docs.typo3.org/typo3cms/TCAReference/stable/Reference/Columns/Select/)


.. _fluidtypo3-flux-field-select_arguments:

Arguments
=========


.. _field.select_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the attribute, FlexForm XML-valid tag name string

.. _field.select_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _field.select_default:

default
-------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Default value for this attribute

.. _field.select_native:

native
------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _field.select_position:

position
--------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _field.select_required:

required
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this attribute must be filled when editing the FCE

.. _field.select_exclude:

exclude
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _field.select_transform:

transform
---------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _field.select_enabled:

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

.. _field.select_requestupdate:

requestUpdate
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, the form is force-saved and reloaded when field value changes

.. _field.select_displaycond:

displayCond
-----------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _field.select_inherit:

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

.. _field.select_inheritempty:

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

.. _field.select_clear:

clear
-----

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _field.select_protect:

protect
-------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, a "protect value" checkbox is displayed next to the field which when checked, protects the value from being changed if the (normally inherited) field value is changed in a parent record. Has no effect if "inherit" is disabled on the field.

.. _field.select_variables:

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

.. _field.select_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _field.select_config:

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

.. _field.select_validate:

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

.. _field.select_size:

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

.. _field.select_multiple:

multiple
--------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, allows selecting the same value multiple times

.. _field.select_minitems:

minItems
--------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Minimum required number of items to be selected

.. _field.select_maxitems:

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

.. _field.select_itemliststyle:

itemListStyle
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default list style when maxItems > 1

.. _field.select_selectedliststyle:

selectedListStyle
-----------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _field.select_items:

items
-----

:aspect:`DataType`
   mixed

:aspect:`Required`
   true
:aspect:`Description`
   Items for the selector; array / CSV / Traversable / Query supported

.. _field.select_emptyoption:

emptyOption
-----------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _field.select_translatecsvitems:

translateCsvItems
-----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _field.select_itemsprocfunc:

itemsProcFunc
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _field.select_rendertype:

renderType
----------

:aspect:`DataType`
   string

:aspect:`Default`
   'selectSingle'

:aspect:`Required`
   false
:aspect:`Description`
   Rendering type as applies in FormEngine/TCA

.. _field.select_showicontable:

showIconTable
-------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   If TRUE shows the option icons as table beneath the select
