:navigation-title: grid
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid:

=============================
grid ViewHelper `<flux:grid>`
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


.. _fluidtypo3-flux-grid_arguments:

Arguments
=========


.. _grid_name:

name
----

:aspect:`DataType`
   string

:aspect:`Default`
   'grid'

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of this grid - defaults to "grid"

.. _grid_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional label for this grid - defaults to an LLL value (reported if it is missing)

.. _grid_variables:

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

.. _grid_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
