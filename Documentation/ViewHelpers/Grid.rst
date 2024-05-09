..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/GridViewHelper.php

:edit-on-github-link: GridViewHelper.php
:navigation-title: grid
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid:

=============================
Grid ViewHelper `<flux:grid>`
=============================

Grid container ViewHelper.

Use `<flux:grid.row>` with nested `<flux:grid.column>` tags
to define a tabular layout.

The grid is then rendered automatically in the preview section
of the content element, or as page columns if used in page templates.

For frontend rendering, use `flux:content.render`.

Define Page and Content elements
================================

Name is used to identify columns and fetch e.g. translations from XLF files.

    <flux:grid>
        <flux:grid.row>
            <flux:grid.column colPos="0" name="Main" colspan="3" style="width: 75%" />
            <flux:grid.column colPos="1" name="Secondary" colspan="1" style="width: 25%" />
        </flux:grid.row>
    </flux:grid>

Rendering
---------

    <v:content.render column="0" />
    <v:content.render column="1" />

.. _fluidtypo3-flux-grid_source:

Source code
===========

Go to the source code of this ViewHelper: `GridViewHelper.php (GitHub) <fluidtypo3/flux/development/GridViewHelper.php>`__.

.. _fluidtypo3-flux-grid_arguments:

Arguments
=========

The following arguments are available for `<flux:grid>`:

..  contents::
    :local:


.. _fluidtypo3-flux-grid-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-grid-name
    :type: string
    :Default: 'grid'
    :required: false

    Optional name of this grid - defaults to "grid"

.. _fluidtypo3-flux-grid-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-grid-label
    :type: string
    :required: false

    Optional label for this grid - defaults to an LLL value (reported if it is missing)

.. _fluidtypo3-flux-grid-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-grid-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template

.. _fluidtypo3-flux-grid-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-grid-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
