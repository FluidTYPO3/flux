..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Content/RenderViewHelper.php

:edit-on-github-link: Content/RenderViewHelper.php
:navigation-title: content.render
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-content-render:

=================================================
Content.render ViewHelper `<flux:content.render>`
=================================================

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

.. _fluidtypo3-flux-content-render_source:

Source code
===========

Go to the source code of this ViewHelper: `RenderViewHelper.php (GitHub) <fluidtypo3/flux/development/Content/RenderViewHelper.php>`__.

.. _fluidtypo3-flux-content-render_arguments:

Arguments
=========

The following arguments are available for `<flux:content.render>`:

..  contents::
    :local:


.. _fluidtypo3-flux-content-render-area_argument:

area
----

..  confval:: area
    :name: fluidtypo3-flux-content-render-area
    :type: string
    :required: true

    Name or "colPos" value of the content area to render

.. _fluidtypo3-flux-content-render-limit_argument:

limit
-----

..  confval:: limit
    :name: fluidtypo3-flux-content-render-limit
    :type: integer
    :required: false

    Optional limit to the number of content elements to render

.. _fluidtypo3-flux-content-render-offset_argument:

offset
------

..  confval:: offset
    :name: fluidtypo3-flux-content-render-offset
    :type: integer
    :required: false

    Optional offset to the limit

.. _fluidtypo3-flux-content-render-order_argument:

order
-----

..  confval:: order
    :name: fluidtypo3-flux-content-render-order
    :type: string
    :Default: 'sorting'
    :required: false

    Optional sort order of content elements - RAND() supported

.. _fluidtypo3-flux-content-render-sortdirection_argument:

sortDirection
-------------

..  confval:: sortDirection
    :name: fluidtypo3-flux-content-render-sortdirection
    :type: string
    :Default: 'ASC'
    :required: false

    Optional sort direction of content elements

.. _fluidtypo3-flux-content-render-as_argument:

as
--

..  confval:: as
    :name: fluidtypo3-flux-content-render-as
    :type: string
    :required: false

    Variable name to register, then render child content and insert all results as an array of records

.. _fluidtypo3-flux-content-render-loadregister_argument:

loadRegister
------------

..  confval:: loadRegister
    :name: fluidtypo3-flux-content-render-loadregister
    :type: mixed
    :required: false

    List of LOAD_REGISTER variable

.. _fluidtypo3-flux-content-render-render_argument:

render
------

..  confval:: render
    :name: fluidtypo3-flux-content-render-render
    :type: boolean
    :Default: true
    :required: false

    Optional returning variable as original table rows

.. _fluidtypo3-flux-content-render-hideuntranslated_argument:

hideUntranslated
----------------

..  confval:: hideUntranslated
    :name: fluidtypo3-flux-content-render-hideuntranslated
    :type: boolean
    :required: false

    Exclude untranslated records
