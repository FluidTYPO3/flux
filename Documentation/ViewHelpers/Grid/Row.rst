..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Grid/RowViewHelper.php

:edit-on-github-link: Grid/RowViewHelper.php
:navigation-title: grid.row
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid-row:

=====================================
Grid.row ViewHelper `<flux:grid.row>`
=====================================

Flexform Grid Row ViewHelper

Used inside `<flux:grid>` tags.
Usually contains `<flux:grid.column>` tags.

See `<flux:grid>` for an example.

.. _fluidtypo3-flux-grid-row_source:

Source code
===========

Go to the source code of this ViewHelper: `RowViewHelper.php (GitHub) <fluidtypo3/flux/development/Grid/RowViewHelper.php>`__.

.. _fluidtypo3-flux-grid-row_arguments:

Arguments
=========

The following arguments are available for `<flux:grid.row>`:

..  contents::
    :local:


.. _fluidtypo3-flux-grid-row-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-grid-row-name
    :type: string
    :Default: 'row'
    :required: false

    Optional name of this row - defaults to "row"

.. _fluidtypo3-flux-grid-row-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-grid-row-label
    :type: string
    :required: false

    Optional label for this row - defaults to an LLL value (reported if it is missing)

.. _fluidtypo3-flux-grid-row-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-grid-row-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-grid-row-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-grid-row-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
