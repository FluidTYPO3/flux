.. include:: /Includes.rst.txt

.. _fluidtypo3-flux-form-content:

============
form.content
============


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

Arguments
=========


.. _form.content_name:

name
----

:aspect:`DataType`
   string

:aspect:`Required`
   true
:aspect:`Description`
   Name of the content area, FlexForm XML-valid tag name string

.. _form.content_label:

label
-----

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   Label for content area, can be LLL: value. Optional - if not specified, Flux tries to detect an LLL label named "flux.fluxFormId.columns.foobar" based on column name, in scope of extension rendering the Flux form.

.. _form.content_extensionname:

extensionName
-------------

:aspect:`DataType`
   string

:aspect:`Required`
   false
:aspect:`Description`
   If provided, enables overriding the extension context for this and all child nodes. The extension name is otherwise automatically detected from rendering context.
