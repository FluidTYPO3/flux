..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/SelectViewHelper.php

:edit-on-github-link: Field/SelectViewHelper.php
:navigation-title: field.select
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-select:

=============================================
Field.select ViewHelper `<flux:field.select>`
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

.. _fluidtypo3-flux-field-select_source:

Source code
===========

Go to the source code of this ViewHelper: `SelectViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/SelectViewHelper.php>`__.

.. _fluidtypo3-flux-field-select_arguments:

Arguments
=========

The following arguments are available for `<flux:field.select>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-select-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-select-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-select-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-select-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-select-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-select-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-select-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-select-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-select-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-select-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-select-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-select-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-select-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-select-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-select-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-select-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-select-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-select-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-select-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-select-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-select-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-select-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-select-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-select-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-select-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-select-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-select-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-select-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-select-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-select-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-select-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-select-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-select-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-select-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-select-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-select-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-select-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-select-size
    :type: integer
    :Default: 1
    :required: false

    Size of the selector box

.. _fluidtypo3-flux-field-select-multiple_argument:

multiple
--------

..  confval:: multiple
    :name: fluidtypo3-flux-field-select-multiple
    :type: boolean
    :required: false

    If TRUE, allows selecting the same value multiple times

.. _fluidtypo3-flux-field-select-minitems_argument:

minItems
--------

..  confval:: minItems
    :name: fluidtypo3-flux-field-select-minitems
    :type: integer
    :required: false

    Minimum required number of items to be selected

.. _fluidtypo3-flux-field-select-maxitems_argument:

maxItems
--------

..  confval:: maxItems
    :name: fluidtypo3-flux-field-select-maxitems
    :type: integer
    :Default: 1
    :required: false

    Maxium allowed number of items to be selected

.. _fluidtypo3-flux-field-select-itemliststyle_argument:

itemListStyle
-------------

..  confval:: itemListStyle
    :name: fluidtypo3-flux-field-select-itemliststyle
    :type: string
    :required: false

    Overrides the default list style when maxItems > 1

.. _fluidtypo3-flux-field-select-selectedliststyle_argument:

selectedListStyle
-----------------

..  confval:: selectedListStyle
    :name: fluidtypo3-flux-field-select-selectedliststyle
    :type: string
    :required: false

    Overrides the default selected list style when maxItems > 1 and renderType is SelectSingle

.. _fluidtypo3-flux-field-select-items_argument:

items
-----

..  confval:: items
    :name: fluidtypo3-flux-field-select-items
    :type: mixed
    :required: true

    Items for the selector; array / CSV / Traversable / Query supported

.. _fluidtypo3-flux-field-select-emptyoption_argument:

emptyOption
-----------

..  confval:: emptyOption
    :name: fluidtypo3-flux-field-select-emptyoption
    :type: mixed
    :required: false

    If not-FALSE, adds one empty option/value pair to the generated selector box and tries to use this property's value (cast to string) as label.

.. _fluidtypo3-flux-field-select-translatecsvitems_argument:

translateCsvItems
-----------------

..  confval:: translateCsvItems
    :name: fluidtypo3-flux-field-select-translatecsvitems
    :type: boolean
    :required: false

    If TRUE, attempts to resolve a LLL label for each value provided as CSV in "items" attribute using convention for lookup "$field.option.123" if given "123" as CSV item value. Field name is determined by normal Flux field name conventions

.. _fluidtypo3-flux-field-select-itemsprocfunc_argument:

itemsProcFunc
-------------

..  confval:: itemsProcFunc
    :name: fluidtypo3-flux-field-select-itemsprocfunc
    :type: string
    :required: false

    Function for serving items. See TCA "select" field "itemsProcFunc" attribute

.. _fluidtypo3-flux-field-select-rendertype_argument:

renderType
----------

..  confval:: renderType
    :name: fluidtypo3-flux-field-select-rendertype
    :type: string
    :Default: 'selectSingle'
    :required: false

    Rendering type as applies in FormEngine/TCA

.. _fluidtypo3-flux-field-select-showicontable_argument:

showIconTable
-------------

..  confval:: showIconTable
    :name: fluidtypo3-flux-field-select-showicontable
    :type: boolean
    :required: false

    If TRUE shows the option icons as table beneath the select
