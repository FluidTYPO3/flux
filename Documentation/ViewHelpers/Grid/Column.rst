:navigation-title: grid.column
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid-column:

===========================================
grid.column ViewHelper `<flux:grid.column>`
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


.. _fluidtypo3-flux-grid-column_arguments:

Arguments
=========


.. _grid.column_name:

name
----

:aspect:`DataType`
   string

:aspect:`Default`
   'column'

:aspect:`Required`
   false
:aspect:`Description`
   Identifies your column and is used to fetch translations from XLF for example.

.. _grid.column_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional column label, will be shown as column header.

.. _grid.column_colpos:

colPos
------

:aspect:`DataType`
   integer

:aspect:`Required`
   true
:aspect:`Description`
   Column number - between 0 and 99, should be unique.

.. _grid.column_colspan:

colspan
-------

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Column span

.. _grid.column_rowspan:

rowspan
-------

:aspect:`DataType`
   integer

:aspect:`Default`
   1

:aspect:`Required`
   false
:aspect:`Description`
   Row span

.. _grid.column_style:

style
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Inline style to add when rendering the column

.. _grid.column_variables:

variables
---------

:aspect:`DataType`
   mixed

:aspect:`Default`
   array ()

:aspect:`Required`
   false
:aspect:`Description`
   Freestyle variables which become assigned to the resulting Component - can then be read from that Component outside this Fluid template and in other templates using the Form object from this template. Can also be set and/or overridden in tag content using `<flux:form.variable />`

.. _grid.column_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
