:navigation-title: grid.row
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-grid-row:

=====================================
grid.row ViewHelper `<flux:grid.row>`
=====================================


Flexform Grid Row ViewHelper

Used inside `<flux:grid>` tags.
Usually contains `<flux:grid.column>` tags.

See `<flux:grid>` for an example.


.. _fluidtypo3-flux-grid-row_arguments:

Arguments
=========


.. _grid.row_name:

name
----

:aspect:`DataType`
   string

:aspect:`Default`
   'row'

:aspect:`Required`
   false
:aspect:`Description`
   Optional name of this row - defaults to "row"

.. _grid.row_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Optional label for this row - defaults to an LLL value (reported if it is missing)

.. _grid.row_variables:

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

.. _grid.row_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
