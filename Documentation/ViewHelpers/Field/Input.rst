..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Field/InputViewHelper.php

:edit-on-github-link: Field/InputViewHelper.php
:navigation-title: field.input
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field-input:

===========================================
Field.input ViewHelper `<flux:field.input>`
===========================================

Input FlexForm field ViewHelper

.. _fluidtypo3-flux-field-input_source:

Source code
===========

Go to the source code of this ViewHelper: `InputViewHelper.php (GitHub) <fluidtypo3/flux/development/Field/InputViewHelper.php>`__.

.. _fluidtypo3-flux-field-input_arguments:

Arguments
=========

The following arguments are available for `<flux:field.input>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-input-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-input-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-input-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-input-label
    :type: string
    :required: false

    Label for the attribute, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.fields.foobar" based on field name, in scope of extension rendering the Flux form. If field is in an object, use "flux.fluxFormId.objects.objectname.foobar" where "foobar" is the name of the field.

.. _fluidtypo3-flux-field-input-default_argument:

default
-------

..  confval:: default
    :name: fluidtypo3-flux-field-input-default
    :type: string
    :required: false

    Default value for this attribute

.. _fluidtypo3-flux-field-input-native_argument:

native
------

..  confval:: native
    :name: fluidtypo3-flux-field-input-native
    :type: boolean
    :required: false

    If TRUE, this field will treated as a native TCA field (requiring a matching SQL column). If the "name" of this field is an already existing field, that original field will be replaced by this field. If the field is a new field (which doesn't already exist in TCA). You can control where this field visually appears in the editing form by specifying the "position" argument, which supports the same syntax as \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Note that when declaring a field as "native" it will no longer be rendered as part of the FlexForm where Flux fields are normally rendered.

.. _fluidtypo3-flux-field-input-position_argument:

position
--------

..  confval:: position
    :name: fluidtypo3-flux-field-input-position
    :type: string
    :required: false

    Only applies if native=1. Specify where in the editing form this field should be, using the syntax of \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes (after:X before:X and replace:X). Additionally, allows you to specify a TCA sheet if you want this field to be positioned in a dedicated sheet. Examples: position="after:header", position="replace:header", position="after:header My Sheet"

.. _fluidtypo3-flux-field-input-required_argument:

required
--------

..  confval:: required
    :name: fluidtypo3-flux-field-input-required
    :type: boolean
    :required: false

    If TRUE, this attribute must be filled when editing the FCE

.. _fluidtypo3-flux-field-input-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-input-exclude
    :type: boolean
    :required: false

    If TRUE, this field becomes an "exclude field" (see TYPO3 documentation about this)

.. _fluidtypo3-flux-field-input-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-input-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-input-enabled_argument:

enabled
-------

..  confval:: enabled
    :name: fluidtypo3-flux-field-input-enabled
    :type: boolean
    :Default: true
    :required: false

    If FALSE, disables the field in the FlexForm

.. _fluidtypo3-flux-field-input-requestupdate_argument:

requestUpdate
-------------

..  confval:: requestUpdate
    :name: fluidtypo3-flux-field-input-requestupdate
    :type: boolean
    :required: false

    If TRUE, the form is force-saved and reloaded when field value changes

.. _fluidtypo3-flux-field-input-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-input-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-input-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-input-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-input-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-input-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-input-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-input-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-input-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-field-input-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-field-input-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-input-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-field-input-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-input-config
    :type: mixed
    :Default: array ()
    :required: false

    Raw TCA options - passed directly to "config" section of created field and overrides anything generated by the component itself. Can be used to provide options that Flux itself does not support, and can be used to pass root-level arguments for a "userFunc"

.. _fluidtypo3-flux-field-input-eval_argument:

eval
----

..  confval:: eval
    :name: fluidtypo3-flux-field-input-eval
    :type: string
    :Default: 'trim'
    :required: false

    FlexForm-type validation configuration for this input

.. _fluidtypo3-flux-field-input-size_argument:

size
----

..  confval:: size
    :name: fluidtypo3-flux-field-input-size
    :type: integer
    :Default: 32
    :required: false

    Size of field

.. _fluidtypo3-flux-field-input-maxcharacters_argument:

maxCharacters
-------------

..  confval:: maxCharacters
    :name: fluidtypo3-flux-field-input-maxcharacters
    :type: integer
    :required: false

    Maximum number of characters allowed

.. _fluidtypo3-flux-field-input-minimum_argument:

minimum
-------

..  confval:: minimum
    :name: fluidtypo3-flux-field-input-minimum
    :type: integer
    :required: false

    Minimum value for integer type fields

.. _fluidtypo3-flux-field-input-maximum_argument:

maximum
-------

..  confval:: maximum
    :name: fluidtypo3-flux-field-input-maximum
    :type: integer
    :required: false

    Maximum value for integer type fields

.. _fluidtypo3-flux-field-input-placeholder_argument:

placeholder
-----------

..  confval:: placeholder
    :name: fluidtypo3-flux-field-input-placeholder
    :type: string
    :required: false

    Placeholder text which vanishes if field is filled and/or field is focused
