..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/ObjectViewHelper.php

:edit-on-github-link: Form/ObjectViewHelper.php
:navigation-title: form.object
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-object:

===========================================
Form.object ViewHelper `<flux:form.object>`
===========================================

FlexForm field section object ViewHelper

Use this inside flux:form.section to name and divide the fields
into individual objects that can be inserted into the section.

.. _fluidtypo3-flux-form-object_source:

Source code
===========

Go to the source code of this ViewHelper: `ObjectViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/ObjectViewHelper.php>`__.

.. _fluidtypo3-flux-form-object_arguments:

Arguments
=========

The following arguments are available for `<flux:form.object>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-object-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-form-object-name
    :type: string
    :required: true

    Name of the section object, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-form-object-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-object-label
    :type: string
    :required: false

    Label for section object, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.objects.foobar" based on object name, in scope of extension rendering the Flux form.

.. _fluidtypo3-flux-form-object-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-form-object-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-form-object-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-object-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.

.. _fluidtypo3-flux-form-object-inherit_argument:

inherit
-------

..  confval:: inherit
    :name: fluidtypo3-flux-form-object-inherit
    :type: boolean
    :required: false

    If TRUE, the value for this particular field is inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-form-object-inheritempty_argument:

inheritEmpty
------------

..  confval:: inheritEmpty
    :name: fluidtypo3-flux-form-object-inheritempty
    :type: boolean
    :required: false

    If TRUE, allows empty values (specifically excluding the number zero!) to be inherited - if inheritance is enabled by the ConfigurationProvider

.. _fluidtypo3-flux-form-object-transform_argument:

transform
---------

..  confval:: transform
    :name: fluidtypo3-flux-form-object-transform
    :type: string
    :required: false

    Set this to transform your value to this type - integer, array (for csv values), float, DateTime, Vendor\MyExt\Domain\Model\Object or ObjectStorage with type hint.

.. _fluidtypo3-flux-form-object-contentcontainer_argument:

contentContainer
----------------

..  confval:: contentContainer
    :name: fluidtypo3-flux-form-object-contentcontainer
    :type: boolean
    :required: false

    If TRUE, each object that is created of this type results in a content column of the same name, with an automatic colPos value.
