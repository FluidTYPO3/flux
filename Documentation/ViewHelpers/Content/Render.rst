.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-content-render:

==============
content.render
==============


Renders all child content of a record based on the area name.

The `area` is the `name` attribute of the `<grid.column>` that shall
be rendered.

Example: Render all child elements of one grid column
=====================================================

`fluidcontent` element with one column of child elements:

    <f:section name="Configuration">
     <flux:grid>
      <flux:grid.row>
       <flux:grid.column name="teaser" colPos="0"/>
      </flux:grid.row>
     </flux:grid>
    </f:section>

    <f:section name="Main">
     <div style="border: 1px solid red">
      <flux:content.render area="teaser"/>
     </div>
    </f:section>

Arguments
=========


.. _content.render_area:

area
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name or "colPos" value of the content area to render

.. _content.render_limit:

limit
-----

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Optional limit to the number of content elements to render

.. _content.render_offset:

offset
------

:aspect:`DataType`
   integer

:aspect:`Required`
   false
:aspect:`Description`
   Optional offset to the limit

.. _content.render_order:

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

.. _content.render_sortdirection:

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

.. _content.render_as:

as
--

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Variable name to register, then render child content and insert all results as an array of records

.. _content.render_loadregister:

loadRegister
------------

:aspect:`DataType`
   mixed

:aspect:`Required`
   false
:aspect:`Description`
   List of LOAD_REGISTER variable

.. _content.render_render:

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

.. _content.render_hideuntranslated:

hideUntranslated
----------------

:aspect:`DataType`
   boolean

:aspect:`Required`
   false
:aspect:`Description`
   Exclude untranslated records
