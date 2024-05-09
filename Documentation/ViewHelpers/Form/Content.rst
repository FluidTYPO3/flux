..  This reStructured text file has been automatically generated, do not change.
..  Source: fluidtypo3/flux/development/Form/ContentViewHelper.php

:edit-on-github-link: Form/ContentViewHelper.php
:navigation-title: form.content
.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-content:

=============================================
Form.content ViewHelper `<flux:form.content>`
=============================================

Adds a content area to a source using Flux FlexForms

Only works to insert a single content area into your element.
To insert multiple content areas, use instead a full `flux:grid`
with your desired row and column structure; each column then
becomes a content area.

Using `flux:grid` after this ViewHelper in the same `flux:form`
will overwrite this ViewHelper.

Using this ViewHelper after `flux:grid` will cause this ViewHelper
to be ignored.

Example of difference
=====================

    <flux:form id="myform">
        <!-- Creates a basic Grid with one row and one column, names
             the column "mycontent" and makes Flux use this Grid -->
        <flux:content name="mycontent" />
        <!-- Additional flux:content tags are completely ignored -->
    </flux:form>

    <flux:form id="myform">
        <!-- Creates a full, multi-column/row Grid -->
        <flux:grid>
            <flux:grid.row>
                <flux:grid.column name="mycontentA" colPos="0" />
                <flux:grid.column name="mycontentB" colPos="1" />
            </flux:grid.row>
            <flux:grid.row>
                <flux:grid.column name="mycontentC" colPos="2" colspan="2" />
            </flux:grid.row>
        </flux:grid>
        <!-- No use of flux:content is possible after this point -->
    </flux:form>

.. _fluidtypo3-flux-form-content_source:

Source code
===========

Go to the source code of this ViewHelper: `ContentViewHelper.php (GitHub) <fluidtypo3/flux/development/Form/ContentViewHelper.php>`__.

.. _fluidtypo3-flux-form-content_arguments:

Arguments
=========

The following arguments are available for `<flux:form.content>`:

..  contents::
    :local:


.. _fluidtypo3-flux-form-content-name_argument:

name
----

..  confval:: name
    :name: fluidtypo3-flux-form-content-name
    :type: string
    :required: true

    Name of the content area, FlexForm XML-valid tag name string

.. _fluidtypo3-flux-form-content-label_argument:

label
-----

..  confval:: label
    :name: fluidtypo3-flux-form-content-label
    :type: string
    :required: false

    Label for content area, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.columns.foobar" based on column name, in scope of extension rendering the Flux form.

.. _fluidtypo3-flux-form-content-extensionname_argument:

extensionName
-------------

..  confval:: extensionName
    :name: fluidtypo3-flux-form-content-extensionname
    :type: string
    :required: false

    If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
