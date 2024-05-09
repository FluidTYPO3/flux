..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Content/GetViewHelper.php

:edit-on-github-link: Content/GetViewHelper.php
:navigation-title: content.get
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-content-get:

===========================================
Content.get ViewHelper `<flux:content.get>`
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

.. _fluidtypo3-flux-content-get_source:

Source code
===========

Go to the source code of this ViewHelper: `GetViewHelper.php (GitHub) <fluidtypo3/flux/development/Content/GetViewHelper.php>`__.

.. _fluidtypo3-flux-content-get_arguments:

Arguments
=========

The following arguments are available for `<flux:content.get>`:

..  contents::
    :local:


.. _fluidtypo3-flux-content-get-area_argument:

area
----

..  confval:: area
    :name: fluidtypo3-flux-content-get-area
    :type: string
    :required: true

    Name or "colPos" value of the content area to render

.. _fluidtypo3-flux-content-get-limit_argument:

limit
-----

..  confval:: limit
    :name: fluidtypo3-flux-content-get-limit
    :type: integer
    :required: false

    Optional limit to the number of content elements to render

.. _fluidtypo3-flux-content-get-offset_argument:

offset
------

..  confval:: offset
    :name: fluidtypo3-flux-content-get-offset
    :type: integer
    :required: false

    Optional offset to the limit

.. _fluidtypo3-flux-content-get-order_argument:

order
-----

..  confval:: order
    :name: fluidtypo3-flux-content-get-order
    :type: string
    :Default: 'sorting'
    :required: false

    Optional sort order of content elements - RAND() supported

.. _fluidtypo3-flux-content-get-sortdirection_argument:

sortDirection
-------------

..  confval:: sortDirection
    :name: fluidtypo3-flux-content-get-sortdirection
    :type: string
    :Default: 'ASC'
    :required: false

    Optional sort direction of content elements

.. _fluidtypo3-flux-content-get-as_argument:

as
--

..  confval:: as
    :name: fluidtypo3-flux-content-get-as
    :type: string
    :required: false

    Variable name to register, then render child content and insert all results as an array of records

.. _fluidtypo3-flux-content-get-loadregister_argument:

loadRegister
------------

..  confval:: loadRegister
    :name: fluidtypo3-flux-content-get-loadregister
    :type: mixed
    :required: false

    List of LOAD_REGISTER variable

.. _fluidtypo3-flux-content-get-render_argument:

render
------

..  confval:: render
    :name: fluidtypo3-flux-content-get-render
    :type: boolean
    :Default: true
    :required: false

    Optional returning variable as original table rows

.. _fluidtypo3-flux-content-get-hideuntranslated_argument:

hideUntranslated
----------------

..  confval:: hideUntranslated
    :name: fluidtypo3-flux-content-get-hideuntranslated
    :type: boolean
    :required: false

    Exclude untranslated records
