..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Grid/ColumnViewHelper.php

:edit-on-github-link: Grid/ColumnViewHelper.php
:navigation-title: grid.column
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid-column:

===========================================
Grid.column ViewHelper `<flux:grid.column>`
===========================================

Flexform Grid Column ViewHelper

Used inside `<flux:grid.row>` tags.

Use the `colPos` attribute for grids in page and content elements.

See `<flux:grid>` for an example.

Limit allowed elements
======================

It is possible to limit the elements allowed in the column by setting
the `allowedContentTypes` variable:

    <flux:grid.column name="elements" colPos="0">
        <flux:form.variable name="allowedContentTypes" value="text,shortcut"/>
    </flux:grid.column>

The value is a comma-separated list of content type IDs; they can be found
in `tt_content.CType` column.

Limit allowed fluid content elements
====================================

It is also possible to limit the allowed fluid content elements:

    <flux:grid.column name="elements" colPos="0">
        <flux:form.variable name="allowedContentTypes" value="extkey_vehicledetailssectionusedcarseal"/>
    </flux:grid.column>

.. _fluidtypo3-flux-grid-column_source:

Source code
===========

Go to the source code of this ViewHelper: `ColumnViewHelper.php (GitHub) <fluidtypo3/flux/development/Grid/ColumnViewHelper.php>`__.

.. _fluidtypo3-flux-grid-column_arguments:

Arguments
=========

The following arguments are available for `<flux:grid.column>`:

..  contents::
    :local:


.. _fluidtypo3-flux-grid-column-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-grid-column-name
    :type: string
    :Default: 'column'
    :required: false

    Identifies your column and is used to fetch translations from XLF for example.

.. _fluidtypo3-flux-grid-column-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-grid-column-label
    :type: string
    :required: false

    Optional column label, will be shown as column header.

.. _fluidtypo3-flux-grid-column-colpos_argument:

colPos
------

..  confval:: colPos
    :name: fluidtypo3-flux-grid-column-colpos
    :type: integer
    :required: true

    Column number - between 0 and 99, should be unique.

.. _fluidtypo3-flux-grid-column-colspan_argument:

colspan
-------

..  confval:: colspan
    :name: fluidtypo3-flux-grid-column-colspan
    :type: integer
    :Default: 1
    :required: false

    Column span

.. _fluidtypo3-flux-grid-column-rowspan_argument:

rowspan
-------

..  confval:: rowspan
    :name: fluidtypo3-flux-grid-column-rowspan
    :type: integer
    :Default: 1
    :required: false

    Row span

.. _fluidtypo3-flux-grid-column-style_argument:

style
-----

..  confval:: style
    :name: fluidtypo3-flux-grid-column-style
    :type: string
    :required: false

    Inline style to add when rendering the column

.. _fluidtypo3-flux-grid-column-variables_argument:

variables
---------

..  confval:: variables
    :name: fluidtypo3-flux-grid-column-variables
    :type: mixed
    :Default: array ()
    :required: false

    Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template. Can also be set and/or overridden in tag content using `<flux:form.variable />`

.. _fluidtypo3-flux-grid-column-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-grid-column-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
