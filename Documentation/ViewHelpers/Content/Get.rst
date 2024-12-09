:navigation-title: content.get
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-content-get:

===========================================
content.get ViewHelper `<flux:content.get>`
===========================================


Gets all child content of a record based on area.

The elements are already rendered, they just need to be output.

Example: Render all child elements with a border
================================================

`fluidcontent` element with one column of child elements.
Each element gets a red border:

    <f:section name="Configuration">
     <flux:grid>
      <flux:grid.row>
       <flux:grid.column name="teaser" colPos="0"/>
      </flux:grid.row>
     </flux:grid>
    </f:section>

    <f:section name="Main">
     <f:for each="{flux:content.get(area:'teaser')}" as="element">
      <div style="border: 1px solid red">
       <f:format.raw>{element}</f:format.raw>
      </div>
     </f:for>
    </f:section>


.. _fluidtypo3-flux-content-get_arguments:

Arguments
=========


.. _content.get_area:

area
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name or "colPos" value of the content area to render

.. _content.get_limit:

limit
-----

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Optional limit to the number of content elements to render

.. _content.get_offset:

offset
------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Optional offset to the limit

.. _content.get_order:

order
-----

:aspect:`DataType`
   string

:aspect:`Default`
   'sorting'

:aspect:`Required`
   false
:aspect:`Description`
   Optional sort order of content elements - RAND() supported

.. _content.get_sortdirection:

sortDirection
-------------

:aspect:`DataType`
   string

:aspect:`Default`
   'ASC'

:aspect:`Required`
   false
:aspect:`Description`
   Optional sort direction of content elements

.. _content.get_as:

as
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Variable name to register, then render child content and insert all results as an array of records

.. _content.get_loadregister:

loadRegister
------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   List of LOAD_REGISTER variable

.. _content.get_render:

render
------

:aspect:`DataType`
   boolean

:aspect:`Default`
   true

:aspect:`Required`
   false
:aspect:`Description`
   Optional returning variable as original table rows

.. _content.get_hideuntranslated:

hideUntranslated
----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Exclude untranslated records
