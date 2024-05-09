..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/FieldViewHelper.php

:edit-on-github-link: FieldViewHelper.php
:navigation-title: field
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-field:

===============================
Field ViewHelper `<flux:field>`
===============================

FlexForm field ViewHelper

Defines a single field data structure.

.. _fluidtypo3-flux-field_source:

Source code
===========

Go to the source code of this ViewHelper: `FieldViewHelper.php (GitHub) <fluidtypo3/flux/development/FieldViewHelper.php>`__.

.. _fluidtypo3-flux-field_arguments:

Arguments
=========

The following arguments are available for `<flux:field>`:

..  contents::
    :local:


.. _fluidtypo3-flux-field-type_argument:

type
----

..  confval:: type
    :name: fluidtypo3-flux-field-type
    :type: string
    :required: true

    TCA field type

.. _fluidtypo3-flux-field-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-field-name
    :type: string
    :required: true

    Name of the attribute, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-field-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-field-label
    :type: string
    :required: false

    Label for field

.. _fluidtypo3-flux-field-description_argument:

description
-----------

..  confval:: description
    :name: fluidtypo3-flux-field-description
    :type: string
    :required: false

    Field description

.. _fluidtypo3-flux-field-exclude_argument:

exclude
-------

..  confval:: exclude
    :name: fluidtypo3-flux-field-exclude
    :type: boolean
    :required: false

    Set to FALSE if field is not an "exclude" field

.. _fluidtypo3-flux-field-config_argument:

config
------

..  confval:: config
    :name: fluidtypo3-flux-field-config
    :type: mixed
    :Default: array ()
    :required: false

    TCA "config" array

.. _fluidtypo3-flux-field-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-field-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-field-onchange_argument:

onChange
--------

..  confval:: onChange
    :name: fluidtypo3-flux-field-onchange
    :type: string
    :required: false

    TCA onChange instruction

.. _fluidtypo3-flux-field-displaycond_argument:

displayCond
-----------

..  confval:: displayCond
    :name: fluidtypo3-flux-field-displaycond
    :type: string
    :required: false

    Optional "Display Condition" (TCA style) for this particular field. See: https://docs.typo3.org/typo3cms/TCAReference/Reference/Columns/Index.html#displaycond

.. _fluidtypo3-flux-field-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-field-inherit
    :type: boolean
    :Default: true
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-field-inheritempty
    :type: boolean
    :Default: true
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-field-clear_argument:

clear
-----

..  confval:: clear
    :name: fluidtypo3-flux-field-clear
    :type: boolean
    :required: false

    If TRUE, a "clear value" checkbox is displayed next to the field which when checked, completely destroys the current field value all the way down to the stored XML value

.. _fluidtypo3-flux-field-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-field-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
