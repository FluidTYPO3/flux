..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/SheetViewHelper.php

:edit-on-github-link: Form/SheetViewHelper.php
:navigation-title: form.sheet
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-sheet:

=========================================
Form.sheet ViewHelper `<flux:form.sheet>`
=========================================

FlexForm sheet ViewHelper

Groups FlexForm fields into sheets.

.. _fluidtypo3-flux-form-sheet_source:

Source code
===========

Go to the source code of this ViewHelper: `SheetViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/SheetViewHelper.php>`__.

.. _fluidtypo3-flux-form-sheet_arguments:

Arguments
=========

The following arguments are available for `<flux:form.sheet>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-sheet-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-form-sheet-name
    :type: string
    :required: true

    Name of the group, used as FlexForm sheet name, must be FlexForm XML-valid tag name string

.. _fluidtypo3-flux-form-sheet-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-sheet-label
    :type: string
    :required: false

    Label for the field group - used as tab name in FlexForm. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.sheets.foobar" based on sheet name, in scope of extension rendering the Flux form.

.. _fluidtypo3-flux-form-sheet-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-form-sheet-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-form-sheet-description_argument:

description
-----------

..  confval:: description
    :name: fluidtypo3-flux-form-sheet-description
    :type: string
    :required: false

    Optional string or LLL reference with a desription of the purpose of the sheet

.. _fluidtypo3-flux-form-sheet-shortdescription_argument:

shortDescription
----------------

..  confval:: shortDescription
    :name: fluidtypo3-flux-form-sheet-shortdescription
    :type: string
    :required: false

    Optional shorter version of description of purpose of the sheet, LLL reference supported

.. _fluidtypo3-flux-form-sheet-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-sheet-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
