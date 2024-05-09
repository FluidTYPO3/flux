..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/TextViewHelper.php

:edit-on-github-link: Field/TextViewHelper.php
:navigation-title: field.text
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-text:

=========================================
Field.text ViewHelper `<flux:field.text>`
=========================================

Textarea FlexForm field ViewHelper

.. _fluidtypo3-flux-field-text_source:

Source code
===========

Go to the source code of this ViewHelper: `TextViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/TextViewHelper.php>`__.

.. _fluidtypo3-flux-field-text_arguments:

Arguments
=========

The following arguments are available for `<flux:field.text>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-text-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-text-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-text-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-text-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-text-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-text-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-text-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-text-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-text-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-text-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-text-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-text-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-text-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-text-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-text-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-text-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-text-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-text-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-text-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-text-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-text-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-text-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-text-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-text-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-text-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-text-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-text-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-text-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-text-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-text-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-text-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-text-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-text-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-text-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-text-validate_argument:

validate
--------

..  confval:: validate
    :name: fluidtypo3-flux-field-text-validate
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-text-cols_argument:

cols
----

..  confval:: cols
    :name: fluidtypo3-flux-field-text-cols
    :type: mixed
    :Default: 85
    :required: false

    Number of columns in editor

.. _fluidtypo3-flux-field-text-rows_argument:

rows
----

..  confval:: rows
    :name: fluidtypo3-flux-field-text-rows
    :type: mixed
    :Default: 10
    :required: false

    Number of rows in editor

.. _fluidtypo3-flux-field-text-defaultextras_argument:

defaultExtras
-------------

..  confval:: defaultExtras
    :name: fluidtypo3-flux-field-text-defaultextras
    :type: string
    :required: false

    DEPRECATED, IGNORED - has no function on TYPO3 8.7+. FlexForm-syntax "defaultExtras" definition, example: "richtext[*]:rte_transform[mode=ts_css]"

.. _fluidtypo3-flux-field-text-enablerichtext_argument:

enableRichText
--------------

..  confval:: enableRichText
    :name: fluidtypo3-flux-field-text-enablerichtext
    :type: boolean
    :required: false

    Enable the richtext editor (RTE)

.. _fluidtypo3-flux-field-text-rendertype_argument:

renderType
----------

..  confval:: renderType
    :name: fluidtypo3-flux-field-text-rendertype
    :type: string
    :required: false

    Render type allows you to modify the behaviour of text field. At the moment only t3editor and none (works as disabled) are supported but you can create your own. More information: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Text/Index.html#rendertype

.. _fluidtypo3-flux-field-text-format_argument:

format
------

..  confval:: format
    :name: fluidtypo3-flux-field-text-format
    :type: string
    :required: false

    Format is used with renderType and, at the moment, is just useful if renderType is equals to t3editor. At the moment possible values are:  html, typoscript, javascript, css, xml, html, php, sparql, mixed. More information: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Text/Index.html#format

.. _fluidtypo3-flux-field-text-richtextconfiguration_argument:

richtextConfiguration
---------------------

..  confval:: richtextConfiguration
    :name: fluidtypo3-flux-field-text-richtextconfiguration
    :type: string
    :required: false

    Specifies which configuration to use in combination with EXT:rte_ckeditor.If none is given, PageTSconfig "RTE.tx_flux.preset" and "RTE.default.preset" are used.More information: https://docs.typo3.org/typo3cms/TCAReference/ColumnsConfig/Properties/TextRichtextConfiugration.html

.. _fluidtypo3-flux-field-text-placeholder_argument:

placeholder
-----------

..  confval:: placeholder
    :name: fluidtypo3-flux-field-text-placeholder
    :type: string
    :required: false

    Placeholder text which vanishes if field is filled and/or field is focused
